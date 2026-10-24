<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the dash_pending_live_changes and dash_live_export_batches tables if they exist.
 * These were created by earlier migrations (2026_10_16_000010/000020) before the live-sync
 * feature was removed. Safe to run on both fresh and existing Docker databases.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('dash_pending_live_changes');
        Schema::dropIfExists('dash_live_export_batches');
    }

    public function down(): void
    {
        // Intentionally not recreating: this feature was permanently removed.
    }
};
