<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dateTime('expires_at')->nullable();
            $table->tinyInteger('extended')->default(0);
            $table->tinyInteger('unlocked')->default(0);
            $table->integer('renewed_count')->default(0);
            $table->tinyInteger('notified_24h')->default(0);
            $table->tinyInteger('notified_2h')->default(0);
            $table->tinyInteger('notified_closed')->default(0);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->integer('streak_days')->default(0);
            $table->date('streak_date')->nullable();
        });
        // Posts viejos: ciclo desde su creación (quedan cerrados, no borrados)
        DB::statement("UPDATE posts SET expires_at = DATE_ADD(created_at, INTERVAL 72 HOUR) WHERE expires_at IS NULL");
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'extended', 'unlocked',
                'renewed_count', 'notified_24h', 'notified_2h', 'notified_closed']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['streak_days', 'streak_date']);
        });
    }
};
