<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Populates dash_minigames with the nine minigames that are implemented in the
 * socket server. All start with gamepack_id = null (globally available).
 * Run once after the dash_minigames migration: php artisan db:seed --class=DashMinigamesSeeder
 */
class DashMinigamesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // These four are available without any gamepack (guests and pack-less users).
        // gamepack_id = null means globally available.
        $baseMinigames = [
            ['key' => 'dice',          'name' => 'Dice',               'min_players' => 2, 'sort' => 1],
            ['key' => 'wirecutter',    'name' => 'Wirecutter',         'min_players' => 2, 'sort' => 2],
            ['key' => 'cardsofmisery', 'name' => 'Cards of Misery',    'min_players' => 2, 'sort' => 3],
            ['key' => 'misfortune',    'name' => 'Wheel of Misfortune','min_players' => 2, 'sort' => 4],
        ];

        foreach ($baseMinigames as $mg) {
            DB::table('dash_minigames')->updateOrInsert(
                ['key' => $mg['key']],
                [
                    'name'        => $mg['name'],
                    'min_players' => $mg['min_players'],
                    'max_players' => null,
                    'gamepack_id' => null,
                    'is_active'   => true,
                    'settings'    => null,
                    'sort'        => $mg['sort'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]
            );
        }

        // These five require a gamepack. Register them here so they can be assigned
        // to a gamepack via the dashboard; set gamepack_id once the pack exists.
        $packMinigames = [
            ['key' => 'wouldyourather', 'name' => 'Would You Rather',   'min_players' => 2, 'sort' => 5],
            ['key' => 'liar',           'name' => 'Liar',               'min_players' => 3, 'sort' => 6],
            ['key' => 'spitthis',       'name' => 'Spit This!',         'min_players' => 2, 'sort' => 7],
            ['key' => 'buildcocktail',  'name' => 'Build That Cocktail','min_players' => 2, 'sort' => 8],
            ['key' => 'beerburglar',    'name' => 'Beer Burglar',       'min_players' => 2, 'sort' => 9],
        ];

        foreach ($packMinigames as $mg) {
            // Only insert if not already present — don't overwrite a gamepack_id
            // that was set via the dashboard.
            DB::table('dash_minigames')->insertOrIgnore([
                'key'         => $mg['key'],
                'name'        => $mg['name'],
                'min_players' => $mg['min_players'],
                'max_players' => null,
                'gamepack_id' => null,
                'is_active'   => false,
                'settings'    => null,
                'sort'        => $mg['sort'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }
}
