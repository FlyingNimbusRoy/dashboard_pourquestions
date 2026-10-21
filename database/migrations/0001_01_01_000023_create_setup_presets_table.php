<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Game-owned table `setup_presets`, reproduced from the game's schema dump. */
return new class extends Migration
{
    public function up(): void
    {
        $mode = DB::selectOne('select @@SESSION.sql_mode as m')->m;
        DB::statement("set SESSION sql_mode = '".str_replace(['NO_ZERO_DATE', 'NO_ZERO_IN_DATE', 'STRICT_TRANS_TABLES', 'STRICT_ALL_TABLES'], '', $mode)."'");

        Schema::create('setup_presets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('name', 24);
            $table->unsignedTinyInteger('game_length')->default(10);
            $table->boolean('is_nsfw')->default(true);
            $table->boolean('burst_mode')->default(false);
            $table->json('excluded_packs')->nullable();
            $table->json('excluded_categories')->nullable();
            $table->timestamp('created_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
            $table->index('user_id', 'setup_presets_user_id_index');
        });

        DB::statement("set SESSION sql_mode = '$mode'");
    }

    public function down(): void
    {
        Schema::dropIfExists('setup_presets');
    }
};
