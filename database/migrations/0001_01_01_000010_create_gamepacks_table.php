<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `gamepacks`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gamepacks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->enum('category', ['gamepack', 'questionpack', 'skinpack'])->default('gamepack');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->string('url_coverart')->nullable()->default('none');
            $table->string('flavor_text')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gamepacks');
    }
};
