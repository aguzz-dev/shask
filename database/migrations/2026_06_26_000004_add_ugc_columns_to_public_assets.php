<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_assets', function (Blueprint $table) {
            // Propietario del diseño UGC (NULL = asset del sistema, sin propietario de usuario)
            $table->unsignedBigInteger('submitter_user_id')->nullable()->after('id');

            // Estado del ciclo de moderación post-publicación.
            // El default 'approved' preserva la visibilidad de los assets existentes.
            $table->enum('status', ['pending', 'approved', 'rejected', 'reported', 'removed'])
                  ->default('approved')
                  ->after('submitter_user_id');

            // Contador de adquisiciones (hype o rewarded-ad)
            $table->unsignedInteger('downloads_count')->default(0)->after('status');

            // Flag de destacado para el back office
            $table->tinyInteger('is_featured')->default(0)->after('downloads_count');

            // Índice para las consultas de catálogo que filtran por status
            $table->index('status', 'idx_public_assets_status');
        });
    }

    public function down(): void
    {
        Schema::table('public_assets', function (Blueprint $table) {
            $table->dropIndex('idx_public_assets_status');
            $table->dropColumn(['submitter_user_id', 'status', 'downloads_count', 'is_featured']);
        });
    }
};
