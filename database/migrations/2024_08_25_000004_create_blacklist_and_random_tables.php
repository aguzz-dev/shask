<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklist_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('ip', 45);
            $table->string('random_user', 20);
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        Schema::create('preguntas_random', function (Blueprint $table) {
            $table->id();
            $table->text('pregunta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas_random');
        Schema::dropIfExists('blacklist_user');
    }
};
