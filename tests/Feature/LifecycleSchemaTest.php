<?php

use App\Database;

it('posts y users tienen las columnas del ciclo de vida', function () {
    $db = new Database;
    $postCols = array_column(
        $db->query('DESCRIBE posts')->fetch_all(MYSQLI_ASSOC), 'Field');
    $userCols = array_column(
        $db->query('DESCRIBE users')->fetch_all(MYSQLI_ASSOC), 'Field');

    expect($postCols)->toContain('expires_at', 'extended', 'unlocked',
        'renewed_count', 'notified_24h', 'notified_2h', 'notified_closed')
        ->and($userCols)->toContain('streak_days', 'streak_date');
});

it('los posts existentes tienen expires_at backfilleado', function () {
    $db = new Database;
    $row = $db->query('SELECT COUNT(*) AS c FROM posts WHERE expires_at IS NULL')->fetch_assoc();
    expect((int) $row['c'])->toBe(0);
});
