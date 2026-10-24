<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dash_rollup_daily', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date')->unique();
            $table->unsignedSmallInteger('games_total')->default(0);
            $table->unsignedSmallInteger('games_completed')->default(0);
            $table->unsignedSmallInteger('games_ended_early')->default(0);
            $table->unsignedSmallInteger('games_abandoned')->default(0);
            $table->unsignedSmallInteger('unique_hosts')->default(0);
            $table->unsignedSmallInteger('total_player_sessions')->default(0);
            $table->unsignedInteger('avg_duration_s')->nullable();
            $table->decimal('replay_rate', 5, 4)->nullable();
            $table->decimal('completion_rate', 5, 4)->nullable();
            $table->timestamp('computed_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dash_rollup_daily');
    }
};
