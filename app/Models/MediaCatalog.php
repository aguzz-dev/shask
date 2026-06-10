<?php

namespace App\Models;

use App\Database;

class MediaCatalog extends Database
{
    /**
     * Devuelve packs + imágenes del catálogo.
     * Si la tabla media_images está vacía, cae al escaneo del directorio
     * public/images para no romper antes de correr el seeder.
     */
    public function getCatalog()
    {
        $count = $this->query("SELECT COUNT(*) as c FROM media_images")
            ->fetch_assoc()['c'] ?? 0;

        if ((int) $count === 0) {
            return [
                'packs'  => [],
                'images' => $this->scanDirectory(),
            ];
        }

        $packs = $this->query(
            "SELECT id, name, slug, is_premium, price, cover, sort
             FROM sticker_packs ORDER BY sort ASC, id ASC"
        )->fetch_all(MYSQLI_ASSOC);

        $images = $this->query(
            "SELECT mi.id, mi.name, mi.type, mi.category, mi.tags,
                    mi.pack_id, mi.sort,
                    sp.name AS pack_name, sp.is_premium AS pack_is_premium
             FROM media_images mi
             LEFT JOIN sticker_packs sp ON mi.pack_id = sp.id
             ORDER BY mi.sort ASC, mi.id ASC"
        )->fetch_all(MYSQLI_ASSOC);

        return [
            'packs'  => $packs,
            'images' => $images,
        ];
    }

    /**
     * Fallback: escanea public/images y devuelve los nombres con tipo heurístico.
     */
    private function scanDirectory()
    {
        $directory = public_path('images');
        if (!is_dir($directory)) {
            return [];
        }

        $files  = array_diff(scandir($directory), ['.', '..']);
        $images = [];
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
                continue;
            }
            $name  = pathinfo($file, PATHINFO_FILENAME);
            $lower = strtolower($name);
            $isBg  = str_contains($lower, 'background')
                || str_contains($lower, 'bg_')
                || str_contains($lower, 'texture')
                || str_contains($lower, 'pattern');

            $images[] = [
                'name'            => $name,
                'type'            => $isBg ? 'background' : 'sticker',
                'category'        => null,
                'tags'            => null,
                'pack_id'         => null,
                'pack_name'       => null,
                'pack_is_premium' => 0,
            ];
        }
        usort($images, fn ($a, $b) => strcmp($a['name'], $b['name']));
        return $images;
    }
}
