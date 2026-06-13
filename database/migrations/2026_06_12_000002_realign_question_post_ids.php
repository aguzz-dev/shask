<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Re-ancla preguntas guardadas con public_posts.id al posts.id real.
        // Filas ya alineadas (pp.id == pp.post_id) quedan igual; huérfanas no matchean.
        // CORRER UNA SOLA VEZ y junto con el deploy del fix de escritura.
        DB::statement(
            'UPDATE questions q
             JOIN public_posts pp ON q.public_post_id = pp.id
             SET q.public_post_id = pp.post_id
             WHERE pp.id != pp.post_id'
        );
    }

    public function down(): void
    {
        // Irreversible sin snapshot; no-op deliberado.
    }
};
