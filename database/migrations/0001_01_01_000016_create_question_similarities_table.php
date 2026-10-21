<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `question_similarities`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_similarities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('question_id');
            $table->unsignedInteger('similar_question_id');
            $table->float('similarity_score', 0);
            $table->boolean('handled')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['question_id', 'similar_question_id'], 'uq_question_similarity');
            $table->index('similar_question_id', 'fk_question_similarities_similar_question');
            $table->foreign('question_id', 'fk_question_similarities_question')->references('id')->on('questions')->cascadeOnDelete();
            $table->foreign('similar_question_id', 'fk_question_similarities_similar_question')->references('id')->on('questions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_similarities');
    }
};
