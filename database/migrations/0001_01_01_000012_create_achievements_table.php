<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `achievements`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('image');
            $table->enum('criteria_type', ['games_played', 'games_won', 'total_shots', 'shot_highscore', 'single_game_shots', 'member_number']);
            $table->unsignedInteger('criteria_value')->default(0);
            $table->boolean('exact_match')->default(false);
            $table->unsignedInteger('character_id')->nullable();
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('character_id', 'fk_achievements_character');
            $table->foreign('character_id', 'fk_achievements_character')->references('id')->on('characters')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
