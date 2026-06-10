<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Packs de stickers/fondos (free o premium).
        Schema::create('sticker_packs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_premium')->default(false);
            $table->integer('price')->default(0); // en "hype" (moneda interna)
            $table->string('cover')->nullable();  // imagen de portada del pack
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        // Catálogo de imágenes (stickers y fondos) con metadata.
        Schema::create('media_images', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // clave de archivo en public/images
            $table->string('type')->default('sticker'); // sticker | background
            $table->string('category')->nullable(); // ej: amor, fiesta, gotico
            $table->json('tags')->nullable();        // ["amor","corazon"]
            $table->unsignedBigInteger('pack_id')->nullable()->index();
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        // Packs que posee cada usuario (compras).
        Schema::create('pack_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pack_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->dateTime('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_user');
        Schema::dropIfExists('media_images');
        Schema::dropIfExists('sticker_packs');
    }
};
