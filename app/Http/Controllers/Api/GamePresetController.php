<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GamePresetResource;
use App\Services\Crud\GamePresetService;
use Illuminate\Http\Request;
use App\Http\Requests\GamePreset\StoreGamePresetRequest;
use App\Http\Requests\GamePreset\UpdateGamePresetRequest;

class GamePresetController extends Controller
{
    public function index(Request $request, GamePresetService $service)
    {
        return GamePresetResource::collection($service->list($request->query(), min((int) $request->query('per_page', 25), 200)));
    }

    public function show(GamePresetService $service, int $id)
    {
        return new GamePresetResource($service->find($id));
    }

    public function store(StoreGamePresetRequest $request, GamePresetService $service)
    {
        return (new GamePresetResource($service->create($request->validated())))->response()->setStatusCode(201);
    }

    public function update(UpdateGamePresetRequest $request, GamePresetService $service, int $id)
    {
        return new GamePresetResource($service->update($service->find($id), $request->validated()));
    }

    public function destroy(GamePresetService $service, int $id)
    {
        $service->delete($service->find($id));

        return response()->noContent();
    }
}
