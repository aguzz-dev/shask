<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_assets', function (Blueprint $table) {
            $table->id();
            $table->json('color')->nullable();
            $table->string('icon')->nullable();
            $table->string('background')->nullable();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->json('color')->nullable();
            $table->string('icon')->nullable();
            $table->string('background')->nullable();
            $table->integer('price')->default(0);
        });
        // Los assets de usuario tienen id > 10000
        DB::statement('ALTER TABLE assets AUTO_INCREMENT = 10001');

        Schema::create('asset_user', function (Blueprint $table) {
            $table->id();
            $table->integer('asset_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->dateTime('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_user');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('public_assets');
    }
};
