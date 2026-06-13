<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Única puerta de archivos del catálogo de media. Todo el back office
 * escribe/borra a través de esta clase; nada toca public_path('images')
 * directo. Migrar a S3 = configurar un disk s3 y cambiar MEDIA_DISK.
 */
class MediaStorage
{
    private string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.media_disk', 'media');
    }

    public function put(string $name, string $pngBytes): void
    {
        Storage::disk($this->disk)->put("{$name}.png", $pngBytes);
    }

    public function delete(string $name): void
    {
        Storage::disk($this->disk)->delete("{$name}.png");
    }

    public function exists(string $name): bool
    {
        return Storage::disk($this->disk)->exists("{$name}.png");
    }

    /** URL pública de la imagen (hoy vía /api/image; con S3, la del bucket/CDN). */
    public function url(string $name): string
    {
        return rtrim(config('app.url'), '/') . '/api/image/' . $name;
    }
}
