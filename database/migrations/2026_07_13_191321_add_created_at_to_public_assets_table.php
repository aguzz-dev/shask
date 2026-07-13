<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds a nullable `created_at` timestamp to `public_assets` (NEW-badge
     * signal, see marketplace-item-showcase spec).
     *
     * RISK FINDING (task 1.3, verified empirically on this project's MySQL
     * 8.0 instance): `ADD COLUMN created_at TIMESTAMP NULL DEFAULT
     * CURRENT_TIMESTAMP` (i.e. `->nullable()->useCurrent()`) DOES retro-fill
     * every pre-existing row with the ALTER TABLE execution timestamp — it
     * does NOT leave legacy rows NULL, contrary to the initial assumption
     * that a nullable column would be exempt from default backfill. This was
     * confirmed by running the migration once, inspecting existing rows
     * (`SELECT created_at FROM public_assets`), finding them all stamped
     * with the migration's run time, then rolling back.
     *
     * Fix: use a plain `TIMESTAMP NULL DEFAULT NULL` with NO default
     * expression at all. A static NULL default does not get backfilled as a
     * "computed now" — existing AND new rows read NULL unless the
     * application explicitly sets `created_at` at INSERT time. Batch 1 does
     * not wire `Asset::createPublicAsset()` to set it (out of this batch's
     * scope) — new assets will also read NULL until that follow-up lands;
     * documented as a deviation in the apply-progress report.
     */
    public function up(): void
    {
        Schema::table('public_assets', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->after('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('public_assets', function (Blueprint $table) {
            $table->dropColumn('created_at');
        });
    }
};
