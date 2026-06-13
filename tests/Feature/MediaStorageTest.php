<?php

use App\Services\MediaStorage;

it('guarda, comprueba, resuelve url y borra por el disk media', function () {
    $storage = new MediaStorage;
    $name = 'test_ms_' . uniqid();

    expect($storage->exists($name))->toBeFalse();

    $storage->put($name, 'png-bytes-fake');
    expect($storage->exists($name))->toBeTrue()
        ->and(file_exists(public_path("images/{$name}.png")))->toBeTrue()
        ->and($storage->url($name))->toContain("/api/image/{$name}");

    $storage->delete($name);
    expect($storage->exists($name))->toBeFalse()
        ->and(file_exists(public_path("images/{$name}.png")))->toBeFalse();
});
