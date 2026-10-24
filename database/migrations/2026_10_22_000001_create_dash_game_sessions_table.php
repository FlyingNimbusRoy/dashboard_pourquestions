<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dash_game_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('game_id')->unique();
            $table->string('pin', 10);
            $table->unsignedInteger('host_user_id')->nullable()->index();
            $table->string('host_device_id', 64)->nullable();
            $table->enum('status', ['completed', 'ended_early', 'abandoned']);
            $table->unsignedTinyInteger('schema_version')->default(1);
            $table->string('app_version', 20);
            $table->unsignedTinyInteger('player_count')->default(0);
            $table->unsignedSmallInteger('question_count')->default(0);
            $table->datetime('started_at')->nullable();
            $table->datetime('ended_at')->nullable();
            $table->unsignedInteger('duration_s')->nullable();
            $table->unsignedSmallInteger('lead_changes')->default(0);
            $table->string('last_phase_type', 20)->nullable();
            $table->unsignedTinyInteger('last_phase_round')->nullable();
            $table->json('settings');
            $table->json('payload');
            $table->timestamp('ingested_at')->useCurrent();

            $table->index('started_at');
            $table->index('status');
            $table->index('app_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dash_game_sessions');
    }
};
