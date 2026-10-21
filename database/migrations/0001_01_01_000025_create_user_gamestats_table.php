<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `user_gamestats`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_gamestats', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('games_played')->default(0);
            $table->unsignedInteger('games_won')->default(0);
            $table->unsignedInteger('games_lost')->default(0);
            $table->unsignedInteger('total_shots')->default(0);
            $table->unsignedInteger('most_shots')->default(0);
            $table->unsignedInteger('least_shots')->nullable();
            $table->unsignedInteger('shot_highscore')->default(0);
            $table->unsignedInteger('question_highscore')->default(0);
            $table->json('modifiers')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique('user_id', 'unique_user_gamestats');
            $table->foreign('user_id', 'user_gamestats_ibfk_1')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_gamestats');
    }
};
