<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `player_game_log`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_game_log', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('game_id')->nullable();
            $table->unsignedSmallInteger('shots')->default(0);
            $table->unsignedSmallInteger('answered_correct')->default(0);
            $table->unsignedSmallInteger('answered_wrong')->default(0);
            $table->boolean('won')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['user_id', 'created_at'], 'player_game_log_user_created_index');
            $table->foreign('user_id', 'player_game_log_user_id_foreign')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('game_id', 'player_game_log_game_id_foreign')->references('id')->on('games')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_game_log');
    }
};
