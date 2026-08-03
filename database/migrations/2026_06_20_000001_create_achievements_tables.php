<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Recreates two tables that exist in production but were never captured by a
// migration (achievements/achievement_user predate this file — reward was
// added on top of them by 2026_06_26_000003_add_reward_to_achievements.php,
// which ALTERs a table this migration is now responsible for creating).
// Column shapes are reverse-engineered from App\Models\Achievement,
// database\seeders\AchievementSeeder and App\Models\UserStats.
//
// Guarded with hasTable() because both tables already exist (created by
// hand, outside migrations) on every environment that predates this file —
// dev and the current production DB included. Only a genuinely fresh
// database will actually run the Schema::create calls below.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('achievements')) {
            Schema::create('achievements', function (Blueprint $table) {
                $table->id();

                $table->string('code', 50)->unique();
                $table->integer('weight')->notNull();
                $table->string('emoji', 10)->notNull();
                $table->string('name_es', 100)->notNull();
                $table->string('name_en', 100)->notNull();
                $table->boolean('active')->notNull()->default(true);
            });
        }

        if (!Schema::hasTable('achievement_user')) {
            Schema::create('achievement_user', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->notNull();
                $table->unsignedBigInteger('achievement_id')->notNull();
                $table->timestamp('unlocked_at')->useCurrent();

                $table->primary(['user_id', 'achievement_id']);

                $table->foreign('user_id', 'fk_achv_user')
                      ->references('id')
                      ->on('users')
                      ->cascadeOnDelete();

                $table->foreign('achievement_id', 'fk_achv_achievement')
                      ->references('id')
                      ->on('achievements')
                      ->cascadeOnDelete();
            });
        }
    }

    // Intentionally a no-op: since up() may not have created these tables
    // (pre-existing on most environments), rolling back must never drop
    // tables this migration didn't create — that would be real data loss
    // on dev/production, the exact hazard this migration exists to avoid.
    public function down(): void
    {
    }
};
