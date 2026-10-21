<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `user_achievements`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('achievement_id');
            $table->timestamp('unlocked_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['user_id', 'achievement_id'], 'unique_user_achievement');
            $table->index('achievement_id', 'achievement_id');
            $table->foreign('user_id', 'user_achievements_ibfk_1')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('achievement_id', 'user_achievements_ibfk_2')->references('id')->on('achievements')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
    }
};
