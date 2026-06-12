<?php

use App\Database;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $this->username = "web_{$suffix}";
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype)
        VALUES ('Web Test', '{$this->username}', 'web_{$suffix}@test.com', 'x', 1200)");
    $this->userId = $this->db->dbConnection->insert_id;

    // Buzón habilitado (status=1, no vencido). asset_id=2 existe en
    // public_assets con color [[r,g,b,a],...]: cubre el formato real.
    $this->db->query("INSERT INTO posts (title, asset_id, user_id, status, created_at)
        VALUES ('Buzon visible', 2, {$this->userId}, 1, CURDATE())");
    $this->visiblePostId = $this->db->dbConnection->insert_id;
    $this->db->query("INSERT INTO public_posts (post_id, user_id, url)
        VALUES ({$this->visiblePostId}, {$this->userId}, 'wv{$this->userId}')");
    $this->visiblePublicId = $this->db->dbConnection->insert_id;

    // Buzón oculto (status=0): NO debe listarse
    $this->db->query("INSERT INTO posts (title, asset_id, user_id, status, created_at)
        VALUES ('Buzon oculto', 1, {$this->userId}, 0, CURDATE())");
    $this->hiddenPostId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM public_posts WHERE id = {$this->visiblePublicId}");
    $this->db->query("DELETE FROM posts WHERE id IN ({$this->visiblePostId}, {$this->hiddenPostId})");
    $this->db->query("DELETE FROM achievement_user WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

it('muestra el perfil con buzones habilitados y solo logros desbloqueados', function () {
    $response = $this->get("/@{$this->username}");

    $response->assertOk()
        ->assertSee('@' . strtoupper($this->username), false)
        ->assertSee('Buzon visible')
        ->assertDontSee('Buzon oculto')
        ->assertSee('1K de hype')       // hype 1200 => desbloqueado por evaluate()
        ->assertDontSee('10K de hype')  // bloqueado: no se presume
        ->assertSee('rgba(', false);    // sticker pintado con el color del asset
});

it('devuelve 404 amigable para username inexistente', function () {
    $this->get('/@no_existe_nadie_xyz')->assertNotFound();
});
