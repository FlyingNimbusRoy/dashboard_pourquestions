<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dash_pending_live_changes', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 64);
            $table->unsignedBigInteger('record_id');
            $table->string('column_name', 64);
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('exported_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dash_pending_live_changes');
    }
};
