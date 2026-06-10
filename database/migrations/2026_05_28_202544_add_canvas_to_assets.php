<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── Nuevas columnas en public_assets ──────────────────────────────
        Schema::table('public_assets', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id');
            // canvas almacena el CanvasDesign JSON completo (share card 1:1)
            $table->json('canvas')->nullable()->after('background');
        });

        // ── Nuevas columnas en assets (usuario) ───────────────────────────
        Schema::table('assets', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id');
            $table->json('canvas')->nullable()->after('background');
        });

        // ── Limpiar todos los assets existentes ───────────────────────────
        // Los nuevos se crearán desde la app con el sistema de capas v3.
        DB::table('asset_user')->truncate();
        DB::table('assets')->truncate();
        DB::table('public_assets')->truncate();
    }

    public function down(): void
    {
        Schema::table('public_assets', function (Blueprint $table) {
            $table->dropColumn(['title', 'canvas']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['title', 'canvas']);
        });
    }
};
