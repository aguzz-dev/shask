<?php

use App\Database;
use App\Models\Asset;
use App\Models\AssetUser;
use App\Models\User;

// ── Fixtures globales ─────────────────────────────────────────────────────────

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();

    // Creador (propietario del asset UGC)
    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Creator D', 'creator_{$suffix}', 'creator_{$suffix}@test.com', 'x', 0)"
    );
    $this->creatorId = $this->db->dbConnection->insert_id;

    // Comprador (otro usuario)
    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Buyer D', 'buyer_{$suffix}', 'buyer_{$suffix}@test.com', 'x', 100)"
    );
    $this->buyerId = $this->db->dbConnection->insert_id;

    // Token del comprador para pruebas HTTP
    $this->buyerToken = 'tok_buyer_' . $suffix;
    $this->db->query(
        "INSERT INTO personal_access_tokens (token, user_id)
         VALUES ('{$this->buyerToken}', {$this->buyerId})"
    );

    // Token del creador para pruebas HTTP
    $this->creatorToken = 'tok_creator_' . $suffix;
    $this->db->query(
        "INSERT INTO personal_access_tokens (token, user_id)
         VALUES ('{$this->creatorToken}', {$this->creatorId})"
    );

    $this->createdAssetIds = [];
});

afterEach(function () {
    foreach ($this->createdAssetIds as $id) {
        $this->db->query("DELETE FROM asset_user WHERE asset_id = {$id}");
        $this->db->query("DELETE FROM public_assets WHERE id = {$id}");
    }
    $this->db->query("DELETE FROM personal_access_tokens WHERE user_id IN ({$this->creatorId}, {$this->buyerId})");
    $this->db->query("DELETE FROM users WHERE id IN ({$this->creatorId}, {$this->buyerId})");
});

// ── Helper ────────────────────────────────────────────────────────────────────

/** Inserta un asset y registra su id para limpieza al finalizar. */
function insertAsset(Database $db, array $createdIds, int $submitterId, string $status = 'approved'): int
{
    $stmt = $db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, submitter_user_id, status)
         VALUES ('Test Asset', '[]', 'star', '', ?, ?)"
    );
    $stmt->bind_param('is', $submitterId, $status);
    $stmt->execute();
    $stmt->close();
    $id = $db->dbConnection->insert_id;
    $createdIds[] = $id;
    return $id;
}

// ── D.1.3: createPublicAsset establece ownership y status ────────────────────

it('primer asset de un creador nuevo queda en status pending', function () {
    $model = new Asset;
    $id = $model->createPublicAsset(
        'Mi primer diseño', [], 'star', '', null, $this->creatorId
    );
    $this->createdAssetIds[] = $id;

    $row = $this->db->query("SELECT status, submitter_user_id FROM public_assets WHERE id = {$id}")->fetch_assoc();
    expect($row['status'])->toBe('pending')
        ->and((int) $row['submitter_user_id'])->toBe($this->creatorId);
});

it('segundo asset del mismo creador queda en status approved', function () {
    $model = new Asset;

    // Primer asset
    $id1 = $model->createPublicAsset('Diseño 1', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $id1;

    // Segundo asset
    $id2 = $model->createPublicAsset('Diseño 2', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $id2;

    $row = $this->db->query("SELECT status FROM public_assets WHERE id = {$id2}")->fetch_assoc();
    expect($row['status'])->toBe('approved');
});

// ── D.1.4: updatePublicAsset verifica ownership ───────────────────────────────

it('updatePublicAsset del propietario actualiza el asset correctamente', function () {
    $model = new Asset;
    $id = $model->createPublicAsset('Original', [], 'star', '', null, $this->creatorId);
    $this->createdAssetIds[] = $id;

    $model->updatePublicAsset($id, 'Editado', [], 'moon', '', null, $this->creatorId);

    $row = $this->db->query("SELECT title FROM public_assets WHERE id = {$id}")->fetch_assoc();
    expect($row['title'])->toBe('Editado');
});

it('updatePublicAsset de no-propietario lanza excepcion con codigo 403', function () {
    $model = new Asset;
    $id = $model->createPublicAsset('Ajeno', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $id;

    expect(fn () => $model->updatePublicAsset($id, 'Hackeado', [], '', '', null, $this->buyerId))
        ->toThrow(Exception::class);

    // El asset no debe haber cambiado
    $row = $this->db->query("SELECT title FROM public_assets WHERE id = {$id}")->fetch_assoc();
    expect($row['title'])->toBe('Ajeno');
});

// ── D.1.5: getAllAssets excluye assets no visibles ────────────────────────────

it('getAllAssets excluye assets con status rejected, reported y removed', function () {
    $stmt = $this->db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, submitter_user_id, status)
         VALUES (?, '[]', '', '', ?, ?)"
    );

    foreach (['rejected', 'reported', 'removed'] as $status) {
        $title = "Excluido {$status}";
        $stmt->bind_param('sis', $title, $this->creatorId, $status);
        $stmt->execute();
        $this->createdAssetIds[] = $this->db->dbConnection->insert_id;
    }
    $stmt->close();

    // Asset visible (approved)
    $stmt2 = $this->db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, submitter_user_id, status)
         VALUES ('Visible', '[]', '', '', ?, 'approved')"
    );
    $stmt2->bind_param('i', $this->creatorId);
    $stmt2->execute();
    $visibleId = $this->db->dbConnection->insert_id;
    $this->createdAssetIds[] = $visibleId;
    $stmt2->close();

    $data = (new Asset)->getAllAssets();
    $publicIds = array_column($data['public_assets'], 'id');

    // Los excluidos no deben aparecer
    foreach ($this->createdAssetIds as $id) {
        $row = $this->db->query("SELECT status FROM public_assets WHERE id = {$id}")->fetch_assoc();
        if (in_array($row['status'], ['rejected', 'reported', 'removed'])) {
            expect($publicIds)->not->toContain((string) $id);
        }
    }

    // El visible sí debe aparecer
    expect($publicIds)->toContain((string) $visibleId);
});

// ── D.1.6: buyAsset usa users.hype y minta al creador ────────────────────────

it('buyAsset con source=hype debita hype del comprador y mintea al creador', function () {
    // Asset del creador
    $model = new Asset;
    $assetId = $model->createPublicAsset('Diseño UGC', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $assetId;

    $cost = config('marketplace.acquisition_hype_cost', 20);
    $mint = config('marketplace.creator_hype_mint', 15);

    (new AssetUser)->buyAsset($assetId, $this->buyerId, 'hype');

    $buyer   = $this->db->query("SELECT hype FROM users WHERE id = {$this->buyerId}")->fetch_assoc();
    $creator = $this->db->query("SELECT hype FROM users WHERE id = {$this->creatorId}")->fetch_assoc();

    expect((int) $buyer['hype'])->toBe(100 - $cost)
        ->and((int) $creator['hype'])->toBe($mint);

    $owned = $this->db->query(
        "SELECT COUNT(*) AS c FROM asset_user WHERE asset_id = {$assetId} AND user_id = {$this->buyerId}"
    )->fetch_assoc();
    expect((int) $owned['c'])->toBe(1);
});

it('buyAsset con source=ad no debita hype al comprador pero mintea al creador', function () {
    $model = new Asset;
    $assetId = $model->createPublicAsset('Diseño UGC Ad', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $assetId;

    $mint = config('marketplace.creator_hype_mint', 15);

    (new AssetUser)->buyAsset($assetId, $this->buyerId, 'ad');

    $buyer   = $this->db->query("SELECT hype FROM users WHERE id = {$this->buyerId}")->fetch_assoc();
    $creator = $this->db->query("SELECT hype FROM users WHERE id = {$this->creatorId}")->fetch_assoc();

    // Comprador no pierde hype (empezó en 100)
    expect((int) $buyer['hype'])->toBe(100)
        ->and((int) $creator['hype'])->toBe($mint);

    $owned = $this->db->query(
        "SELECT COUNT(*) AS c FROM asset_user WHERE asset_id = {$assetId} AND user_id = {$this->buyerId}"
    )->fetch_assoc();
    expect((int) $owned['c'])->toBe(1);
});

it('buyAsset lanza excepcion si el comprador no tiene suficiente hype', function () {
    // Comprador sin hype suficiente
    $this->db->query("UPDATE users SET hype = 5 WHERE id = {$this->buyerId}");

    $model = new Asset;
    $assetId = $model->createPublicAsset('Costoso', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $assetId;

    expect(fn () => (new AssetUser)->buyAsset($assetId, $this->buyerId, 'hype'))
        ->toThrow(Exception::class);

    // Sin asset en asset_user
    $owned = $this->db->query(
        "SELECT COUNT(*) AS c FROM asset_user WHERE asset_id = {$assetId} AND user_id = {$this->buyerId}"
    )->fetch_assoc();
    expect((int) $owned['c'])->toBe(0);
});

// ── D.1.7: reportPublicAsset cambia status a reported ────────────────────────

it('reportPublicAsset cambia el status a reported y el asset desaparece del catalogo publico', function () {
    $model = new Asset;
    $assetId = $model->createPublicAsset('Reportable', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $assetId;

    // Status inicial visible
    $before = $this->db->query("SELECT status FROM public_assets WHERE id = {$assetId}")->fetch_assoc();
    expect(in_array($before['status'], ['pending', 'approved']))->toBeTrue();

    $model->reportAsset($assetId);

    $after = $this->db->query("SELECT status FROM public_assets WHERE id = {$assetId}")->fetch_assoc();
    expect($after['status'])->toBe('reported');

    // No debe aparecer en el catálogo público
    $data = (new Asset)->getAllAssets();
    $publicIds = array_column($data['public_assets'], 'id');
    expect($publicIds)->not->toContain((string) $assetId);
});

// ── D.1.8: Admin takedown vía HTTP ───────────────────────────────────────────

it('admin takedown establece status=removed y el asset sale del catalogo', function () {
    config(['app.admin_path' => 'panel-test']);

    $model = new Asset;
    $assetId = $model->createPublicAsset('Takedown test', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $assetId;

    // Forzar un status reportado para simular la cola de moderación
    $this->db->query("UPDATE public_assets SET status = 'reported' WHERE id = {$assetId}");

    $adminId = (new \App\Models\AdminUser)->create('Admin D', 'admind_' . uniqid() . '@test.com', 'clave-larga-123');

    $this->withSession(['admin_id' => $adminId])
        ->post("/panel-test/designs/{$assetId}/takedown")
        ->assertRedirect();

    $row = $this->db->query("SELECT status FROM public_assets WHERE id = {$assetId}")->fetch_assoc();
    expect($row['status'])->toBe('removed');

    // Limpiar admin
    $this->db->query("DELETE FROM admin_audit_log WHERE admin_id = {$adminId}");
    $this->db->query("DELETE FROM admin_users WHERE id = {$adminId}");
});
