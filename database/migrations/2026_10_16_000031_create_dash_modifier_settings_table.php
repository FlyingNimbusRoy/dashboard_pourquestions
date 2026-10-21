<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dash_modifier_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('turnbased')->default(false);
            $table->unsignedInteger('modifier_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('gamepack_id')->nullable()->index();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->foreign('modifier_id')->references('id')->on('modifiers')->nullOnDelete();
            $table->foreign('gamepack_id')->references('id')->on('gamepacks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dash_modifier_settings');
    }
};
