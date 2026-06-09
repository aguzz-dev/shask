<?php

namespace App\Http\Controllers;

use App\Models\MediaCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ImageController extends Controller
{
    public function get($name)
    {
        $directory = public_path('images');
        $files = scandir(directory: $directory);

        $files = array_diff($files, array('.', '..'));

        $filesWithoutExtension = array_map(function($file) {
            return pathinfo($file, PATHINFO_FILENAME);
        }, $files);

        if (in_array($name, $filesWithoutExtension)) {
            return response()->file($directory . '/' . $name.'.png');
        } else {
            return response()->json(['error' => 'Image not found'], 404);
        }
    }

    /**
     * Devuelve el catálogo de imágenes disponibles para el editor de assets
     * (stickers y fondos). La fuente de verdad es la carpeta public/images,
     * por lo que cualquier imagen agregada al directorio aparece sin más pasos.
     *
     * Respuesta:
     *   { "images": [ { "name": "fire", "url": "<base>/api/image/fire", "type": "sticker" }, ... ] }
     *
     * `type` es heurístico (sticker | background). El cliente puede usar todas
     * las imágenes para ambos propósitos; el tipo solo sirve para agrupar.
     */
    public function catalog(): JsonResponse
    {
        $data = (new MediaCatalog)->getCatalog();
        $base = rtrim(config('app.url'), '/');

        // Enriquecer cada imagen con su URL de preview y normalizar tipos.
        $images = array_map(function ($img) use ($base) {
            $tags = $img['tags'] ?? null;
            if (is_string($tags)) {
                $decoded = json_decode($tags, true);
                $tags = is_array($decoded) ? $decoded : [];
            }
            return [
                'name'      => $img['name'],
                'url'       => $base . '/api/image/' . $img['name'],
                'type'      => $img['type'] ?? 'sticker',
                'category'  => $img['category'] ?? null,
                'tags'      => $tags ?? [],
                'pack_id'   => isset($img['pack_id']) ? (int) $img['pack_id'] : null,
                'pack_name' => $img['pack_name'] ?? null,
                'is_premium'=> (int) ($img['pack_is_premium'] ?? 0) === 1,
            ];
        }, $data['images']);

        $packs = array_map(function ($p) use ($base) {
            return [
                'id'         => (int) $p['id'],
                'name'       => $p['name'],
                'slug'       => $p['slug'],
                'is_premium' => (int) $p['is_premium'] === 1,
                'price'      => (int) $p['price'],
                'cover'      => $p['cover'] ? $base . '/api/image/' . $p['cover'] : null,
            ];
        }, $data['packs']);

        return response()->json([
            'packs'  => array_values($packs),
            'images' => array_values($images),
        ]);
    }

    /**
     * Manifest de cache-busting: { "<name>": "<hash8>", ... } con el sha1 corto
     * del contenido de cada imagen de public/images. Cacheado con una clave
     * derivada de la firma del directorio (name+filemtime), así solo se recomputa
     * cuando un archivo se agrega/quita/reemplaza.
     */
    public function manifest(): JsonResponse
    {
        $directory = public_path('images');
        $files = array_values(array_diff(scandir($directory), ['.', '..']));

        $signatureParts = [];
        foreach ($files as $file) {
            $signatureParts[] = $file . ':' . filemtime($directory . '/' . $file);
        }
        $signature = md5(implode('|', $signatureParts));

        $manifest = Cache::remember(
            'images_manifest_' . $signature,
            now()->addDay(),
            function () use ($directory, $files) {
                $out = [];
                foreach ($files as $file) {
                    $name = pathinfo($file, PATHINFO_FILENAME);
                    $out[$name] = substr(sha1_file($directory . '/' . $file), 0, 8);
                }
                return $out;
            }
        );

        return response()->json($manifest);
    }
}
