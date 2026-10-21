<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Services\Crud\GameService;
use Illuminate\Http\Request;
use App\Http\Requests\Game\UpdateGameRequest;

class GameController extends Controller
{
    public function index(Request $request, GameService $service)
    {
        return GameResource::collection($service->list($request->query(), min((int) $request->query('per_page', 25), 200)));
    }

    public function show(GameService $service, int $id)
    {
        return new GameResource($service->find($id));
    }

    public function update(UpdateGameRequest $request, GameService $service, int $id)
    {
        return new GameResource($service->update($service->find($id), $request->validated()));
    }
}
