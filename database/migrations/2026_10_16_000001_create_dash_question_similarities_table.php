<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Separate from the game's own `question_similarities` table: this one keeps the
        // per-signal breakdown and the reviewer's decision, which the game table cannot hold.
        Schema::create('dash_question_similarities', function (Blueprint $table) {
            $table->id();
            // Always stored with question_id < similar_question_id, so each pair exists once.
            $table->unsignedInteger('question_id');
            $table->unsignedInteger('similar_question_id');
            $table->float('score');
            $table->json('signals');
            $table->string('status', 16)->default('open')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['question_id', 'similar_question_id'], 'dash_question_similarities_pair_unique');
            $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete();
            $table->foreign('similar_question_id')->references('id')->on('questions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dash_question_similarities');
    }
};
