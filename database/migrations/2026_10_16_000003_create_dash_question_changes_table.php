<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Audit trail of every change made through the dashboard. No foreign key on
        // question_id on purpose: the history must survive deleting the question.
        Schema::create('dash_question_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('question_id')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 32);
            $table->json('changes');
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dash_question_changes');
    }
};
