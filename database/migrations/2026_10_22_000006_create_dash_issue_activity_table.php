<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dash_issue_activity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('issue_id')->index();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('action', 30);
            $table->string('from_value', 50)->nullable();
            $table->string('to_value', 50)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('issue_id')
                ->references('id')->on('dash_issues')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dash_issue_activity');
    }
};
