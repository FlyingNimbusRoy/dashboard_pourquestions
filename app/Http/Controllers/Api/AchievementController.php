<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AchievementResource;
use App\Services\Crud\AchievementService;
use Illuminate\Http\Request;
use App\Http\Requests\Achievement\StoreAchievementRequest;
use App\Http\Requests\Achievement\UpdateAchievementRequest;

class AchievementController extends Controller
{
    public function index(Request $request, AchievementService $service)
    {
        return AchievementResource::collection($service->list($request->query(), min((int) $request->query('per_page', 25), 200)));
    }

    public function show(AchievementService $service, int $id)
    {
        return new AchievementResource($service->find($id));
    }

    public function store(StoreAchievementRequest $request, AchievementService $service)
    {
        return (new AchievementResource($service->create($request->validated())))->response()->setStatusCode(201);
    }

    public function update(UpdateAchievementRequest $request, AchievementService $service, int $id)
    {
        return new AchievementResource($service->update($service->find($id), $request->validated()));
    }

    public function destroy(AchievementService $service, int $id)
    {
        $service->delete($service->find($id));

        return response()->noContent();
    }
}
