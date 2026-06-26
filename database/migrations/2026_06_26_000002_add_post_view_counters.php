<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Contadores denormalizados en posts
        Schema::table('posts', function (Blueprint $table) {
            $table->integer('views')->notNull()->default(0)->after('renewed_count');
            $table->integer('unique_views')->notNull()->default(0)->after('views');
        });

        // Tabla efímera de deduplicación diaria. Sin FK dura sobre post_id para
        // no bloquear borrados del ciclo de vida. La PK compuesta actúa como
        // índice de la purga por day.
        Schema::create('post_view_dedup', function (Blueprint $table) {
            $table->integer('post_id');
            $table->date('day');
            $table->char('visitor_hash', 64);
            $table->primary(['post_id', 'day', 'visitor_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_view_dedup');

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['views', 'unique_views']);
        });
    }
};
