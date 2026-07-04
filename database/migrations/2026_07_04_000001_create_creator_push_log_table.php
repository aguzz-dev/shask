<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creator_push_log', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('creator_user_id')->notNull();
            $table->date('day')->notNull();

            // Acquisitions counted for the creator on this day.
            $table->integer('acquired_count')->notNull()->default(0);

            // Dedup flags — cap is <=2 pushes/creator/day (one of each).
            $table->boolean('single_sent')->notNull()->default(false);
            $table->boolean('digest_sent')->notNull()->default(false);

            // Individual push suppressed by quiet-hours, awaiting flush.
            // Never discarded — flushed by push:flush-quiet-hours once the
            // quiet-hours window ends (creator-acquisition-push spec).
            $table->boolean('deferred_pending')->notNull()->default(false);

            $table->timestamp('last_push_at')->nullable();

            $table->unique(['creator_user_id', 'day'], 'uq_creator_push_log_creator_day');

            $table->foreign('creator_user_id', 'fk_creator_push_log_creator')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_push_log');
    }
};
