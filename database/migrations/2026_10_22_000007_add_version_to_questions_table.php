<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Incremented by QuestionObserver / AnswerObserver whenever question
            // text, trivia, difficulty, or answer content changes — lets analytics
            // group historical dash_game_question_results by content era.
            $table->unsignedSmallInteger('version')->default(1)->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }
};
