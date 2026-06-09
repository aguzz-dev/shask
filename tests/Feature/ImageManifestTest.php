<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ImageManifestTest extends TestCase
{
    public function test_manifest_devuelve_hash_corto_de_contenido_por_imagen(): void
    {
        $dir = public_path('images');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $name = '__test_manifest__';
        $path = $dir . '/' . $name . '.png';
        $bytes = 'PNG-FAKE-CONTENT-123';
        file_put_contents($path, $bytes);
        Cache::flush();

        try {
            $response = $this->get('/api/images/manifest');
            $response->assertStatus(200);
            $expected = substr(sha1($bytes), 0, 8);
            $response->assertJsonFragment([$name => $expected]);
        } finally {
            @unlink($path);
            Cache::flush();
        }
    }
}
