<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Premium/free split for the ad-consumable model + subscription seam.
 *
 * - public_assets.is_premium: server-authoritative gate. Backfilled so every
 *   existing UGC design (submitter_user_id IS NOT NULL) is premium; system
 *   designs stay free. The client never sends this flag.
 * - users.is_subscriber / subscription_expires_at: seam for the future paid
 *   tier. When active, premium assets are granted without an ad. No billing is
 *   wired yet; the server is the sole source of truth for this state.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_assets', function (Blueprint $table) {
            $table->boolean('is_premium')->default(false)->after('status');
        });

        DB::statement('UPDATE public_assets SET is_premium = 1 WHERE submitter_user_id IS NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_subscriber')->default(false);
            $table->timestamp('subscription_expires_at')->nullable();
        });

        // Premium asset use now also happens via subscription (no ad). Record it
        // in the acquisition ledger so creator earnings still count the use.
        DB::statement("ALTER TABLE asset_acquisitions MODIFY COLUMN source ENUM('hype','ad','sub') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE asset_acquisitions MODIFY COLUMN source ENUM('hype','ad') NOT NULL");

        Schema::table('public_assets', function (Blueprint $table) {
            $table->dropColumn('is_premium');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_subscriber', 'subscription_expires_at']);
        });
    }
};
