<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->enum('type', ['feedback', 'bug', 'support'])->default('support');
            $table->enum('status', ['new', 'in_progress', 'waiting', 'resolved'])->default('new');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('game_id')->nullable()->index();
            $table->string('device_id', 128)->nullable();
            $table->unsignedInteger('assignee_id')->nullable();
            $table->text('staff_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
    }
};
