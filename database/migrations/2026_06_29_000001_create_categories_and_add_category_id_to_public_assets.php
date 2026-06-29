<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create categories lookup table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique()->notNull();
            $table->string('name', 80)->notNull();
            $table->smallInteger('position')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        // Add category_id FK column to public_assets
        Schema::table('public_assets', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('is_featured');

            $table->foreign('category_id', 'fk_pa_category')
                  ->references('id')
                  ->on('categories')
                  ->nullOnDelete();

            $table->index('category_id', 'idx_pa_category');
            $table->index('title', 'idx_pa_title');
            $table->index('downloads_count', 'idx_pa_downloads');
        });
    }

    public function down(): void
    {
        Schema::table('public_assets', function (Blueprint $table) {
            // FK must be dropped before its supporting index
            $table->dropForeign('fk_pa_category');
            $table->dropIndex('idx_pa_category');
            $table->dropIndex('idx_pa_downloads');
            $table->dropIndex('idx_pa_title');
            $table->dropColumn('category_id');
        });

        Schema::dropIfExists('categories');
    }
};
