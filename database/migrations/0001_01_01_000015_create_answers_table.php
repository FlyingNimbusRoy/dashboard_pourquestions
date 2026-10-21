<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `answers`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('question_id');
            $table->string('answer');
            $table->boolean('is_correct')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('question_id', 'answers_question_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
