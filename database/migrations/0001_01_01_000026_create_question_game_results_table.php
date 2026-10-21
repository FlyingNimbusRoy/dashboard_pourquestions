<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `question_game_results`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_game_results', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedInteger('question_id');
            $table->unsignedInteger('game_id');
            $table->unsignedSmallInteger('answered_correct')->default(0);
            $table->unsignedSmallInteger('answered_wrong')->default(0);
            $table->unsignedSmallInteger('answered_timeout')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['question_id', 'game_id'], 'unique_question_game');
            $table->foreign('question_id', 'question_game_results_question_id_foreign')->references('id')->on('questions')->cascadeOnDelete();
            $table->foreign('game_id', 'question_game_results_game_id_foreign')->references('id')->on('games')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_game_results');
    }
};
