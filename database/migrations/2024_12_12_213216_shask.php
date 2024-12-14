<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 255);
            $table->string('username', 255)->unique();
            $table->string('email', 255)->unique();
            $table->unsignedInteger('coins')->default(0);
            $table->string('password', 255);
            $table->text('avatar')->nullable();
            $table->unsignedInteger('status')->default(0);
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
        });
        Schema::create('assets', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('title', 255)->nullable();
            $table->text('background');
            $table->text('icon');
            $table->string('color', 255);
            $table->unsignedInteger('price')->nullable();
        });

        Schema::create('asset_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedTinyInteger('asset_id')->nullable();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->text('token');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->unsignedTinyInteger('status')->default(0);
            $table->unsignedTinyInteger('asset_id')->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('public_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('user_id');
            $table->string('url', 255)->unique();
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('public_post_id');
            $table->string('text', 255);
            $table->string('hint', 255);
            $table->unsignedTinyInteger('status')->default(0);
            $table->foreign('public_post_id')->references('id')->on('posts')->onDelete('cascade');
        });

    }

    public function down()
    {
        Schema::dropIfExists('questions');
        Schema::dropIfExists('public_posts');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('asset_user');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('users');
    }
};
