<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gamepack\StoreGamepackRequest;
use App\Http\Requests\Gamepack\UpdateGamepackRequest;
use App\Http\Resources\GamepackResource;
use App\Services\Crud\GamepackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GamepackController extends Controller
{
    public function __construct(private readonly GamepackService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->query('per_page', 25), 1), 200);

        return GamepackResource::collection($this->service->list($request->only(['search', 'sort', 'direction', 'category']), $perPage));
    }

    public function show(int $gamepack): GamepackResource
    {
        return new GamepackResource($this->service->find($gamepack));
    }

    public function store(StoreGamepackRequest $request): JsonResponse
    {
        $model = $this->service->create($request->validated());

        return (new GamepackResource($this->service->find($model->getKey())))->response()->setStatusCode(201);
    }

    public function update(UpdateGamepackRequest $request, int $gamepack): GamepackResource
    {
        $model = $this->service->update($this->service->find($gamepack), $request->validated());

        return new GamepackResource($this->service->find($model->getKey()));
    }

    public function destroy(int $gamepack): Response
    {
        $this->service->delete($this->service->find($gamepack));

        return response()->noContent();
    }

}
