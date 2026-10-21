<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `characters`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('url');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('gamepack_id')->nullable();
            $table->string('color_primary')->default('#6F1E51');
            $table->string('color_secondary')->default('#1B1464');
            $table->string('color_muted_primary')->default('#ED4C67');
            $table->string('color_muted_secondary')->default('#B53471');
            $table->boolean('show_on_homepage')->default(true);
            $table->unsignedInteger('parent_character_id')->nullable();
            $table->index('gamepack_id', 'characters_gamepack_id_foreign');
            $table->index('parent_character_id', 'characters_parent_character_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
