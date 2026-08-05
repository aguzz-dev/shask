<?php

use App\Database;

function currentConnectedThreads(): int
{
    $probe = new Database;
    $row   = $probe->query("SHOW STATUS LIKE 'Threads_connected'")->fetch_assoc();
    $probe->dbConnection->close();
    return (int) $row['Value'];
}

it('cierra la conexión mysqli cuando la instancia se destruye', function () {
    $baseline = currentConnectedThreads();

    // Abrir varias instancias sin retenerlas — si __destruct() no cierra
    // la conexión, Threads_connected queda por encima del baseline.
    for ($i = 0; $i < 10; $i++) {
        $db = new Database;
        $db->query('SELECT 1');
        unset($db);
    }

    expect(currentConnectedThreads())->toBeLessThanOrEqual($baseline + 1);
});
