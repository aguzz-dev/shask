<?php
namespace App\Services;

/**
 * Valida y lava PNGs subidos al back office. Nunca se guardan los bytes
 * originales del cliente: si el contenido es un PNG real, se re-encodea
 * con GD (payloads polyglot/chunks extra quedan eliminados).
 */
class PngSanitizer
{
    private const PNG_MAGIC = "\x89PNG\r\n\x1a\n";

    /** Devuelve los bytes re-encodeados, o null si no es un PNG válido. */
    public function sanitize(string $bytes): ?string
    {
        if (!str_starts_with($bytes, self::PNG_MAGIC)) {
            return null;
        }
        $image = @imagecreatefromstring($bytes);
        if ($image === false) {
            return null;
        }
        imagesavealpha($image, true);
        ob_start();
        imagepng($image);
        $clean = ob_get_clean();
        imagedestroy($image);
        return $clean === false ? null : $clean;
    }

    /**
     * Normaliza el filename del cliente a una clave [a-z0-9_]+ sin
     * extensión. Se slugifica el path COMPLETO (no solo el basename) y sin
     * puntos ni barras: imposible el path traversal.
     * Devuelve null si no queda nada usable.
     * (`../../etc/passwd` → `etc_passwd`, `Mi Sticker (1).PNG` →
     * `mi_sticker_1`, `///...` → null.)
     */
    public function normalizeName(string $original): ?string
    {
        // Quitar solo la extensión final (si la hay), conservando el resto.
        $base = preg_replace('/\.[a-zA-Z0-9]+$/', '', $original);
        $slug = strtolower($base);
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
        $slug = trim($slug, '_');
        return $slug === '' ? null : $slug;
    }
}
