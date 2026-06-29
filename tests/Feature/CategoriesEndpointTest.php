<?php

use App\Database;

beforeEach(function () {
    $this->db         = new Database;
    $this->insertedIds = [];
});

afterEach(function () {
    // Remove any categories inserted during a test
    foreach ($this->insertedIds as $id) {
        $this->db->query("DELETE FROM categories WHERE id = {$id}");
    }
});

// ── T3.03: GET /api/categories ────────────────────────────────────────────────

it('GET /api/categories returns empty array when no categories exist', function () {
    $response = $this->getJson('/api/categories');

    $response->assertOk()
             ->assertJson(['success' => true, 'categories' => []]);
});

it('GET /api/categories returns all categories with id, name, slug', function () {
    $suffix = uniqid();

    $stmt = $this->db->dbConnection->prepare(
        "INSERT INTO categories (slug, name, position) VALUES (?, ?, ?)"
    );
    $slug1 = "typography-{$suffix}";
    $slug2 = "colors-{$suffix}";
    $pos   = 0;
    $stmt->bind_param('ssi', $slug1, $name1, $pos);
    $name1 = "Typography {$suffix}";
    $stmt->execute();
    $this->insertedIds[] = $this->db->dbConnection->insert_id;

    $stmt->bind_param('ssi', $slug2, $name2, $pos);
    $name2 = "Colors {$suffix}";
    $stmt->execute();
    $this->insertedIds[] = $this->db->dbConnection->insert_id;
    $stmt->close();

    $response = $this->getJson('/api/categories');

    $response->assertOk()
             ->assertJson(['success' => true])
             ->assertJsonStructure(['success', 'categories' => [['id', 'name', 'slug']]]);

    $ids = array_column($response->json('categories'), 'id');
    expect($ids)->toContain($this->insertedIds[0])
                ->toContain($this->insertedIds[1]);
});

it('GET /api/categories does not require authentication', function () {
    // No bearer token — should still return 200
    $response = $this->getJson('/api/categories');
    $response->assertOk();
});
