<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\MediaCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPackController extends Controller
{
    public function index()
    {
        return view('admin.packs.index', ['packs' => (new MediaCatalog)->allPacks()]);
    }

    public function createForm()
    {
        return view('admin.packs.form', ['pack' => null, 'images' => (new MediaCatalog)->searchImages(null, null)]);
    }

    public function create(Request $request)
    {
        $data = $this->validated($request);
        $catalog = new MediaCatalog;
        $slug = Str::slug($data['name'], '_');

        $id = $catalog->insertPack($data['name'], $slug, $data['is_premium'], $data['price'], $data['cover'], $data['sort']);
        AdminAuditLog::log((int) $request->session()->get('admin_id'), 'pack.create', ['id' => $id, 'name' => $data['name']], $request->ip());

        return redirect('/' . config('app.admin_path') . '/packs')->with('ok', 'Pack creado');
    }

    public function editForm(int $id)
    {
        $pack = (new MediaCatalog)->findPackById($id);
        abort_if($pack === null, 404);
        return view('admin.packs.form', ['pack' => $pack, 'images' => (new MediaCatalog)->searchImages(null, null)]);
    }

    public function edit(Request $request, int $id)
    {
        $catalog = new MediaCatalog;
        abort_if($catalog->findPackById($id) === null, 404);
        $data = $this->validated($request);

        $catalog->updatePack($id, $data['name'], $data['is_premium'], $data['price'], $data['cover'], $data['sort']);
        AdminAuditLog::log((int) $request->session()->get('admin_id'), 'pack.edit', ['id' => $id], $request->ip());

        return redirect('/' . config('app.admin_path') . '/packs')->with('ok', 'Pack actualizado');
    }

    public function delete(Request $request, int $id)
    {
        $catalog = new MediaCatalog;
        $pack = $catalog->findPackById($id);
        abort_if($pack === null, 404);

        $catalog->deletePack($id);
        AdminAuditLog::log((int) $request->session()->get('admin_id'), 'pack.delete', ['id' => $id, 'name' => $pack['name']], $request->ip());

        return redirect('/' . config('app.admin_path') . '/packs')->with('ok', 'Pack eliminado (sus imágenes quedaron sin pack)');
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'is_premium' => 'nullable|in:0,1',
            'price' => 'nullable|integer|min:0',
            'cover' => 'nullable|string|max:255',
            'sort' => 'nullable|integer',
        ]);
        return [
            'name' => (string) $request->input('name'),
            'is_premium' => $request->input('is_premium') === '1',
            'price' => (int) $request->input('price', 0),
            'cover' => $request->input('cover') ?: null,
            'sort' => (int) $request->input('sort', 0),
        ];
    }
}
