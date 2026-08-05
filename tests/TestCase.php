<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * PHPUnit retiene cada instancia de TestCase ejecutada hasta el final de
     * la corrida completa — cualquier conexión mysqli guardada en una
     * propiedad (patrón común: `$this->db = new Database` en beforeEach)
     * sobrevive más allá del test y agota max_connections en suites largas.
     * `Database::__destruct()` no alcanza a cerrarla a tiempo por eso mismo.
     */
    protected function tearDown(): void
    {
        if (isset($this->db) && $this->db instanceof \App\Database) {
            try {
                $this->db->dbConnection?->close();
            } catch (\Throwable $e) {
                // Ya pudo haber sido cerrada por el propio __destruct().
            }
        }

        parent::tearDown();
    }
}
