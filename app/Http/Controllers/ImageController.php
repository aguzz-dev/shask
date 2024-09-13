<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
