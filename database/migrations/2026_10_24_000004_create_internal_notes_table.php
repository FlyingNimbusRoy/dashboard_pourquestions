<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dash_internal_notes', function (Blueprint $table) {
            $table->id();
            $table->string('note_type', 64);
            $table->unsignedBigInteger('note_id');
            $table->text('note');
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['note_type', 'note_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dash_internal_notes');
    }
};
