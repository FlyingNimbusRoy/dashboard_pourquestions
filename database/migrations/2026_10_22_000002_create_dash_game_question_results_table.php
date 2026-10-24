<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dash_game_question_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_session_id')->index();
            $table->unsignedInteger('question_id')->index();
            $table->unsignedSmallInteger('question_version')->default(1);
            $table->unsignedInteger('category_id')->nullable()->index();
            $table->unsignedTinyInteger('round');
            $table->unsignedTinyInteger('correct_count')->default(0);
            $table->unsignedTinyInteger('wrong_count')->default(0);
            $table->unsignedTinyInteger('timeout_count')->default(0);
            $table->json('option_counts')->nullable();

            $table->foreign('game_session_id')
                ->references('id')->on('dash_game_sessions')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dash_game_question_results');
    }
};
