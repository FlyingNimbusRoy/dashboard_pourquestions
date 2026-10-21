<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `questions`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('maker_id');
            $table->string('vraag');
            $table->string('trivia')->nullable();
            $table->boolean('is_random')->default(false);
            $table->boolean('is_nsfw')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('last_compared_at')->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('gamepack_id')->nullable();
            $table->integer('difficulty')->default(3);
            $table->timestamp('last_validated')->default('2020-12-31 23:00:00');
            $table->index('maker_id', 'questions_maker_id_foreign');
            $table->index('category_id', 'questions_category_id_foreign');
            $table->index('gamepack_id', 'questions_gamepack_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
