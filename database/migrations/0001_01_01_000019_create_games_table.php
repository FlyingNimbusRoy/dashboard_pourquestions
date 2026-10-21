<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `games`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('shotstaken')->default(0);
            $table->integer('players')->nullable();
            $table->unsignedInteger('game_length')->default(0);
            $table->boolean('is_nsfw')->default(false);
            $table->boolean('is_finished')->default(false);
            $table->boolean('is_cancelled')->default(false);
            $table->unsignedInteger('host_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('sAndb')->nullable()->default('0')->charset('utf8mb3')->collation('utf8mb3_general_ci');
            $table->json('question_array')->nullable();
            $table->index('host_id', 'games_host_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
