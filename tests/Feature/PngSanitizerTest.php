<?php

use App\Services\PngSanitizer;

function makePngBytes(): string
{
    $im = imagecreatetruecolor(4, 4);
    imagesavealpha($im, true);
    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $transparent);
    ob_start();
    imagepng($im);
    return ob_get_clean();
}

it('acepta un PNG real y devuelve bytes re-encodeados', function () {
    $clean = (new PngSanitizer)->sanitize(makePngBytes());
    expect($clean)->not->toBeNull()
        ->and(substr($clean, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
});

it('rechaza bytes que no son PNG aunque digan serlo', function () {
    expect((new PngSanitizer)->sanitize('GIF89a no soy un png'))->toBeNull();
});

it('lava payloads escondidos despues del IEND', function () {
    $dirty = makePngBytes() . '<?php system($_GET["c"]); ?>';
    $clean = (new PngSanitizer)->sanitize($dirty);
    expect($clean)->not->toBeNull()
        ->and(str_contains($clean, '<?php'))->toBeFalse();
});

it('slugifica nombres y bloquea traversal', function () {
    $s = new PngSanitizer;
    expect($s->normalizeName('../../etc/passwd'))->toBe('etc_passwd')
        ->and($s->normalizeName('Mi Sticker (1).PNG'))->toBe('mi_sticker_1')
        ->and($s->normalizeName('fuego🔥'))->toBe('fuego')
        ->and($s->normalizeName('///...'))->toBeNull();
});
