<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dash_minigames', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('gamepack_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('min_players')->nullable();
            $table->unsignedSmallInteger('max_players')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->foreign('gamepack_id')->references('id')->on('gamepacks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dash_minigames');
    }
};
