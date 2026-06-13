<?php

use App\Database;
use App\Models\AdminUser;
use Illuminate\Http\UploadedFile;

function tmpPngFile(string $clientName): UploadedFile
{
    $im = imagecreatetruecolor(4, 4);
    imagesavealpha($im, true);
    imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
    $path = tempnam(sys_get_temp_dir(), 'png');
    imagepng($im, $path);
    return new UploadedFile($path, $clientName, 'image/png', null, true);
}

beforeEach(function () {
    config(['app.admin_path' => 'panel-test']);
    $this->db = new Database;
    $this->adminId = (new AdminUser)->create('Up Test', 'up_' . uniqid() . '@test.com', 'clave-larga-123');
    $this->uploadedNames = [];
});

afterEach(function () {
    foreach ($this->uploadedNames as $name) {
        $this->db->query("DELETE FROM media_images WHERE name = '{$name}'");
        @unlink(public_path("images/{$name}.png"));
    }
    $this->db->query("DELETE FROM admin_audit_log WHERE admin_id = {$this->adminId}");
    $this->db->query("DELETE FROM admin_users WHERE id = {$this->adminId}");
});

it('sube un png, crea el row, guarda el archivo y audita', function () {
    $name = 'up_' . uniqid();
    $this->uploadedNames[] = $name;

    $response = $this->withSession(['admin_id' => $this->adminId])
        ->post('/panel-test/images/upload', [
            'files' => [tmpPngFile("{$name}.png")],
            'type' => 'sticker',
            'category' => 'test',
            'tags' => 'fuego, hype',
        ]);

    $response->assertRedirect('/panel-test');
    expect(file_exists(public_path("images/{$name}.png")))->toBeTrue();

    $row = $this->db->query("SELECT * FROM media_images WHERE name = '{$name}'")->fetch_assoc();
    expect($row)->not->toBeNull()
        ->and($row['type'])->toBe('sticker')
        ->and(json_decode($row['tags'], true))->toBe(['fuego', 'hype']);

    $audit = $this->db->query(
        "SELECT action FROM admin_audit_log WHERE admin_id = {$this->adminId} AND action = 'image.upload'"
    )->fetch_assoc();
    expect($audit)->not->toBeNull();
});

it('rechaza un archivo que no es png real', function () {
    $path = tempnam(sys_get_temp_dir(), 'fake');
    file_put_contents($path, 'no soy un png');
    $fake = new UploadedFile($path, 'malicioso.png', 'image/png', null, true);

    $this->withSession(['admin_id' => $this->adminId])
        ->post('/panel-test/images/upload', ['files' => [$fake], 'type' => 'sticker'])
        ->assertSessionHasErrors();

    $row = $this->db->query("SELECT COUNT(*) AS c FROM media_images WHERE name = 'malicioso'")->fetch_assoc();
    expect((int) $row['c'])->toBe(0);
});

it('rechaza nombre duplicado', function () {
    $name = 'dup_' . uniqid();
    $this->uploadedNames[] = $name;
    $this->db->query("INSERT INTO media_images (name, type) VALUES ('{$name}', 'sticker')");

    $this->withSession(['admin_id' => $this->adminId])
        ->post('/panel-test/images/upload', [
            'files' => [tmpPngFile("{$name}.png")],
            'type' => 'sticker',
        ])->assertSessionHasErrors();
});
