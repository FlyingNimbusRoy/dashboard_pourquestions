<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dash_question_reviews', function (Blueprint $table) {
            // Why a question was denied (App\Enums\DenyReason); `notes` keeps the optional free text.
            $table->string('reason', 24)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('dash_question_reviews', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
};
