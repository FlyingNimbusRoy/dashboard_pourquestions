<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dash_game_question_results', function (Blueprint $table) {
            $table->dropColumn(['answer_ms', 'avg_answer_ms']);
        });
    }

    public function down(): void
    {
        Schema::table('dash_game_question_results', function (Blueprint $table) {
            $table->json('answer_ms')->nullable()->after('option_counts');
            $table->unsignedSmallInteger('avg_answer_ms')->nullable()->after('answer_ms');
        });
    }
};
