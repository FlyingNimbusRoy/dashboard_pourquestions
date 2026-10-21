<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `booze_buddies`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booze_buddies', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['sender_id', 'receiver_id'], 'unique_friendship');
            $table->index('receiver_id', 'receiver_id');
            $table->foreign('sender_id', 'booze_buddies_ibfk_1')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('receiver_id', 'booze_buddies_ibfk_2')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booze_buddies');
    }
};
