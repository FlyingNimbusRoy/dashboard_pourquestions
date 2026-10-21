<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `gamepack_purchased`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gamepack_purchased', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('gamepack_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->boolean('opened')->default(false);
            $table->boolean('access_through_admin_grant')->default(false);
            $table->index('gamepack_id', 'gamepack_purchased_gamepack_id_foreign');
            $table->index('user_id', 'gamepack_purchased_user_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gamepack_purchased');
    }
};
