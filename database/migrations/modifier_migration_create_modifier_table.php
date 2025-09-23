<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('modifiers', function (Blueprint $table) {
            $table->increments('id'); // INT UNSIGNED AUTO_INCREMENT (ipv BIGINT)
            $table->string('name'); // e.g. "Trickster"
            $table->text('description')->nullable(); // placeholder text allowed
            $table->string('fa_icon')->nullable(); // font awesome or custom icon
            $table->boolean('turnbased')->default(false); // ticks with turns or not
            $table->json('effects')->nullable(); // JSON defining the logic

            $table->unsignedInteger('coupled_gamepack_id')->nullable();
            $table->foreign('coupled_gamepack_id')
                ->references('id')
                ->on('gamepacks')
                ->onDelete('cascade'); // lock modifier to a gamepack

            $table->boolean('is_active')->default(true); // toggle active/inactive
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modifiers');
    }
};
