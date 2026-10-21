<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserGamestatsResource;
use App\Services\Crud\UserGamestatsService;
use Illuminate\Http\Request;
use App\Http\Requests\UserGamestats\UpdateUserGamestatsRequest;

class GamestatsController extends Controller
{
    public function index(Request $request, UserGamestatsService $service)
    {
        return UserGamestatsResource::collection($service->list($request->query(), min((int) $request->query('per_page', 25), 200)));
    }

    public function show(UserGamestatsService $service, int $id)
    {
        return new UserGamestatsResource($service->find($id));
    }

    public function update(UpdateUserGamestatsRequest $request, UserGamestatsService $service, int $id)
    {
        return new UserGamestatsResource($service->update($service->find($id), $request->validated()));
    }
}
