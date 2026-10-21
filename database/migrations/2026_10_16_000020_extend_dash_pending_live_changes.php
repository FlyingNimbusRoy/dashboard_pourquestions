<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dash_live_export_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedInteger('change_count');
            $table->string('filename', 128);
            $table->longText('sql');
            $table->timestamp('exported_at')->nullable();
        });

        Schema::table('dash_pending_live_changes', function (Blueprint $table) {
            $table->string('operation', 8)->default('update')->after('id'); // insert|update|delete
            $table->json('changes')->nullable(); // update: {column: [old, new]}
            $table->json('row')->nullable();     // insert: final row; delete: row snapshot
            $table->string('column_name', 64)->nullable()->change();
            $table->unsignedBigInteger('batch_id')->nullable()->index();
            $table->timestamp('discarded_at')->nullable()->index();
        });

        // Convert legacy single-column flag changes into the generic shape.
        foreach (DB::table('dash_pending_live_changes')->whereNotNull('column_name')->get() as $r) {
            DB::table('dash_pending_live_changes')->where('id', $r->id)->update([
                'changes' => json_encode([$r->column_name => [$r->old_value, $r->new_value]]),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('dash_pending_live_changes', function (Blueprint $table) {
            $table->dropColumn(['operation', 'changes', 'row', 'batch_id', 'discarded_at']);
        });
        Schema::dropIfExists('dash_live_export_batches');
    }
};
