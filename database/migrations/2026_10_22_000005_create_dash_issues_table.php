<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dash_issues', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['content', 'balance', 'tech']);
            $table->string('subject_type', 30);
            $table->unsignedInteger('subject_id')->nullable();
            $table->string('reason_code', 50)->index();
            $table->text('reason_summary');
            $table->json('metric_snapshot')->nullable();
            $table->enum('status', ['open', 'assigned', 'in_progress', 'resolved', 'wont_fix'])->default('open');
            $table->unsignedInteger('assignee_id')->nullable()->index();
            $table->unsignedInteger('created_by')->nullable();
            $table->datetime('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dash_issues');
    }
};
