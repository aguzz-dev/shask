<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Composite index supporting the 7-day trending momentum query
     * (`WHERE created_at >= NOW() - INTERVAL ? DAY GROUP BY asset_id`).
     * `created_at` leads because the predicate is a global time-window range
     * scan; `asset_id` follows so the sub-select is covering (only these two
     * columns are read) and supports the GROUP BY.
     */
    public function up(): void
    {
        Schema::table('asset_acquisitions', function (Blueprint $table) {
            $table->index(['created_at', 'asset_id'], 'idx_acq_created_asset');
        });
    }

    public function down(): void
    {
        Schema::table('asset_acquisitions', function (Blueprint $table) {
            $table->dropIndex('idx_acq_created_asset');
        });
    }
};
