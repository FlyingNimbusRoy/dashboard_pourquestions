<?php

namespace App\Services\Insights;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** Owners per gamepack: distinct users in gamepack_purchased (admin grants counted separately). */
class GamepackOwners
{
    /** @return list<array{id:int,name:string,owners:int,grants:int}> sorted by owners desc */
    public function perPack(): array
    {
        return Cache::store('file')->remember('insights.owners.v1', 300, function (): array {
            $rows = DB::select('select g.id, g.name, count(distinct p.user_id) owners,
                count(distinct case when p.access_through_admin_grant = 1 then p.user_id end) grants
                from gamepacks g left join gamepack_purchased p on p.gamepack_id = g.id group by g.id, g.name order by owners desc, g.name');

            return array_map(fn ($r) => ['id' => (int) $r->id, 'name' => $r->name, 'owners' => (int) $r->owners, 'grants' => (int) $r->grants], $rows);
        });
    }
}
