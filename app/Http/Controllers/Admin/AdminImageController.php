<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaCatalog;
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
}
