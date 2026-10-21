<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `online`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('game_id')->nullable();
            $table->boolean('host')->default(false);
            $table->string('user_csrf')->nullable();
            $table->text('action')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online');
    }
};
