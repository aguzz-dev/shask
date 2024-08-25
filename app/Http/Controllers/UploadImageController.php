<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadImageController extends Controller
{
    public function uploadBackground(Request $request)
    {
        $image = $request->file('image');

        $imageName = $image->getClientOriginalName();

        $image->move(public_path('images/background'), $imageName);

        $imageUrl = Storage::url('images/background' . $imageName);

        return response()->json([
            'message' => 'Imagen subida con éxito',
            'image_url' => $imageUrl,
            'original_name' => $imageName,
        ], 200);
    }
}
