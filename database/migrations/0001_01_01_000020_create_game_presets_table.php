<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `game_presets`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_presets', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name', 100);
            $table->unsignedTinyInteger('rounds')->default(10);
            $table->boolean('is_nsfw')->default(false);
            $table->json('pack_ids');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('user_id', 'game_presets_user_id_foreign');
            $table->foreign('user_id', 'game_presets_user_id_foreign')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_presets');
    }
};
