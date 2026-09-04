<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Premium-by-default policy: every catalog design costs one rewarded ad to use
 * unless an admin explicitly frees it from the back office.
 *
 *  - Flip the column default to 1 so new designs are premium out of the box.
 *  - Mark every existing design premium (overrides the earlier UGC-only
 *    backfill from 2026_09_03_000002).
 *
 * Admins free specific designs via /{admin}/designs (is_premium = 0).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE public_assets MODIFY is_premium TINYINT(1) NOT NULL DEFAULT 1');
        DB::statement('UPDATE public_assets SET is_premium = 1');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE public_assets MODIFY is_premium TINYINT(1) NOT NULL DEFAULT 0');
    }
};
