<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_acquisitions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('asset_id')->notNull();
            $table->unsignedBigInteger('buyer_user_id')->notNull();

            $table->enum('source', ['hype', 'ad'])->notNull();
            $table->integer('hype_minted')->notNull()->default(0);

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('asset_id', 'fk_acq_asset')
                  ->references('id')
                  ->on('public_assets')
                  ->cascadeOnDelete();

            $table->foreign('buyer_user_id', 'fk_acq_buyer')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->index('asset_id', 'idx_acq_asset');
            $table->index('buyer_user_id', 'idx_acq_buyer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_acquisitions');
    }
};
