<?php

use App\Database;
use App\Models\UserStats;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $this->email = "stats_{$suffix}@test.com";
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype, avatar, created_at)
        VALUES ('Test Stats', 'stats_{$suffix}', '{$this->email}', 'x', 1500, '{\"a\":1}', '2024-03-08 10:00:00')");
    $this->userId = $this->db->dbConnection->insert_id;

    // Buzón con public_post y 3 preguntas (2 sin responder, 1 respondida)
    $this->db->query("INSERT INTO posts (title, asset_id, user_id, status, created_at)
        VALUES ('Buzon test', 1, {$this->userId}, 1, CURDATE())");
    $this->postId = $this->db->dbConnection->insert_id;
    $this->db->query("INSERT INTO public_posts (post_id, user_id, url)
        VALUES ({$this->postId}, {$this->userId}, 'ts{$this->userId}')");
    $this->publicPostId = $this->db->dbConnection->insert_id;
    $this->db->query("INSERT INTO questions (public_post_id, text, status) VALUES
        ({$this->publicPostId}, 'q1', 0), ({$this->publicPostId}, 'q2', 0), ({$this->publicPostId}, 'q3', 1)");
});

afterEach(function () {
    $this->db->query("DELETE FROM questions WHERE public_post_id = {$this->publicPostId}");
    $this->db->query("DELETE FROM public_posts WHERE id = {$this->publicPostId}");
    $this->db->query("DELETE FROM posts WHERE id = {$this->postId}");
    $this->db->query("DELETE FROM achievement_user WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

it('calcula las stats del usuario', function () {
    $stats = (new UserStats)->forUser($this->userId);

    expect($stats['questions_received'])->toBe(3)
        ->and($stats['questions_answered'])->toBe(1)
        ->and($stats['mailboxes_created'])->toBe(1)
        ->and($stats['member_since'])->toBe('2024-03-08')
        ->and($stats['hype'])->toBe(1500)
        ->and($stats['has_custom_avatar'])->toBeTrue()
        ->and($stats['max_unread_in_a_mailbox'])->toBe(2);
});
