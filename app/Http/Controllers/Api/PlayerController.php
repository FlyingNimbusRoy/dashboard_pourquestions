<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlayerResource;
use App\Services\Crud\PlayerService;
use Illuminate\Http\Request;
use App\Http\Requests\Player\UpdatePlayerRequest;
use App\Http\Resources\GameResource;

class PlayerController extends Controller
{
    public function index(Request $request, PlayerService $service)
    {
        return PlayerResource::collection($service->list($request->query(), min((int) $request->query('per_page', 25), 200)));
    }

    public function show(PlayerService $service, int $id)
    {
        return new PlayerResource($service->find($id));
    }

    public function update(UpdatePlayerRequest $request, PlayerService $service, int $id)
    {
        return new PlayerResource($service->update($service->find($id), $request->validated()));
    }

    public function achievements(int $id, PlayerService $service)
    {
        $player = $service->find($id);

        return response()->json(['data' => $player->achievementUnlocks()->with('achievement')->orderByDesc('unlocked_at')->get()->map(fn ($u) => [
            'achievement_id' => $u->achievement_id,
            'name' => $u->achievement?->name,
            'unlocked_at' => $u->unlocked_at,
        ])->values()]);
    }

    public function games(Request $request, int $id, PlayerService $service)
    {
        $player = $service->find($id);

        return GameResource::collection($player->hostedGames()->orderByDesc('id')->paginate(min((int) $request->query('per_page', 25), 200)));
    }
}
