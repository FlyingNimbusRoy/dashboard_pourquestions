<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dash_review_batches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_at')->nullable();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('dash_question_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_batch_id')->constrained('dash_review_batches')->cascadeOnDelete();
            $table->unsignedInteger('question_id');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('outcome', 16)->default('pending')->index();
            $table->unsignedTinyInteger('difficulty_before');
            $table->unsignedTinyInteger('difficulty_rating')->nullable();
            $table->boolean('answers_correct')->nullable();
            $table->boolean('wording_clear')->nullable();
            $table->boolean('category_correct')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            // Set when a question marked "needs fix" has been corrected and re-approved.
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->unique(['review_batch_id', 'question_id']);
            $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dash_question_reviews');
        Schema::dropIfExists('dash_review_batches');
    }
};
