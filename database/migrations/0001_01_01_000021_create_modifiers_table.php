<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `modifiers`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modifiers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon', 500)->nullable();
            $table->boolean('turnbased')->default(false);
            $table->json('effects')->nullable();
            $table->unsignedInteger('coupled_gamepack_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('coupled_gamepack_id', 'modifiers_coupled_gamepack_id_foreign');
            $table->foreign('coupled_gamepack_id', 'modifiers_coupled_gamepack_id_foreign')->references('id')->on('gamepacks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modifiers');
    }
};
