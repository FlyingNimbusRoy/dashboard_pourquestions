<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dash_live_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('open_rooms')->default(0);
            $table->unsignedSmallInteger('players_online')->default(0);
            $table->unsignedSmallInteger('games_today')->default(0);
            $table->json('rooms_per_phase')->nullable();
            $table->unsignedTinyInteger('failed_post_count')->default(0);
            $table->unsignedTinyInteger('retry_queue_size')->default(0);
            $table->timestamp('snapshot_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dash_live_snapshots');
    }
};
