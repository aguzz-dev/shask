<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\MediaCatalog;
use App\Services\MediaStorage;
use App\Services\PngSanitizer;
use Illuminate\Http\Request;

class AdminImageController extends Controller
{
    public function index(Request $request)
    {
        $catalog = new MediaCatalog;
        return view('admin.images.index', [
            'images' => $catalog->searchImages($request->query('q'), $request->query('type')),
            'q' => (string) $request->query('q', ''),
            'type' => (string) $request->query('type', ''),
        ]);
    }

    public function showUpload()
    {
        return view('admin.images.upload', [
            'packs' => (new MediaCatalog)->allPacks(),
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'files' => 'required|array|min:1|max:10',
            'files.*' => 'required|file|max:1024',
            'type' => 'required|in:sticker,background',
            'category' => 'nullable|string|max:50',
            'tags' => 'nullable|string|max:255',
            'pack_id' => 'nullable|integer',
        ]);

        $catalog = new MediaCatalog;
        $sanitizer = new PngSanitizer;
        $storage = new MediaStorage;

        $packId = $request->filled('pack_id') ? (int) $request->input('pack_id') : null;
        if ($packId !== null && !$catalog->findPackById($packId)) {
            return back()->withErrors(['pack_id' => 'El pack no existe']);
        }
        $tags = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) $request->input('tags', ''))
        )));

        // Pasada 1: validar TODO antes de escribir nada (subida atómica).
        $batch = [];
        foreach ($request->file('files') as $file) {
            $name = $sanitizer->normalizeName($file->getClientOriginalName());
            if ($name === null) {
                return back()->withErrors(['files' => 'Nombre de archivo inválido: ' . $file->getClientOriginalName()]);
            }
            if ($catalog->imageNameExists($name) || $storage->exists($name)) {
                return back()->withErrors(['files' => "Ya existe una imagen llamada '{$name}'"]);
            }
            if (array_key_exists($name, $batch)) {
                return back()->withErrors(['files' => "Nombre repetido en la subida: '{$name}'"]);
            }
            $clean = $sanitizer->sanitize($file->get());
            if ($clean === null) {
                return back()->withErrors(['files' => $file->getClientOriginalName() . ' no es un PNG válido']);
            }
            $batch[$name] = $clean;
        }

        // Pasada 2: persistir.
        $adminId = (int) $request->session()->get('admin_id');
        foreach ($batch as $name => $clean) {
            $storage->put($name, $clean);
            $catalog->insertImage($name, $request->input('type'), $request->input('category'), $tags, $packId);
            AdminAuditLog::log($adminId, 'image.upload', ['name' => $name], $request->ip());
        }

        return redirect('/' . config('app.admin_path'))
            ->with('ok', count($batch) . ' imagen(es) subidas al catálogo');
    }
}
