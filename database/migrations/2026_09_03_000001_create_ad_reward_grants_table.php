<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Server-side ledger for AdMob rewarded ad grants (anti-fraud core).
 *
 * A premium unlock is only honored when a matching row here reaches
 * status='verified' via a signature-checked AdMob SSV callback, and is then
 * atomically flipped to 'consumed' when the reward is spent. This makes a
 * cloned/modified client unable to fake an ad watch: without a real, signed
 * SSV callback there is never a verified grant to consume.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_reward_grants', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->notNull();

            // Opaque single-use token issued to the client, echoed back to AdMob
            // as custom_data, and matched on both the SSV callback and the spend.
            $table->string('nonce', 64)->unique();

            // What this reward unlocks: asset_use | extend | unlock | revive
            $table->string('purpose', 32)->notNull();

            // Optional binding to the target entity (asset_id or post_id) so a
            // reward minted for one purpose/target can't be spent on another.
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->enum('status', ['pending', 'verified', 'consumed'])
                  ->default('pending');

            // AdMob transaction id (dedupes replayed SSV callbacks).
            $table->string('transaction_id', 128)->nullable()->unique();
            $table->string('reward_item', 64)->nullable();
            $table->integer('reward_amount')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('consumed_at')->nullable();

            $table->foreign('user_id', 'fk_arg_user')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->index(['user_id', 'status'], 'idx_arg_user_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_reward_grants');
    }
};
