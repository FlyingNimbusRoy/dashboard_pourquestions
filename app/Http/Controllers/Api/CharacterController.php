<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Character\StoreCharacterRequest;
use App\Http\Requests\Character\UpdateCharacterRequest;
use App\Http\Resources\CharacterResource;
use App\Services\Crud\CharacterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CharacterController extends Controller
{
    public function __construct(private readonly CharacterService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->query('per_page', 25), 1), 200);

        return CharacterResource::collection($this->service->list($request->only(['search', 'sort', 'direction', 'gamepack_id', 'show_on_homepage']), $perPage));
    }

    public function show(int $character): CharacterResource
    {
        return new CharacterResource($this->service->find($character));
    }

    public function store(StoreCharacterRequest $request): JsonResponse
    {
        $model = $this->service->create($request->validated());

        return (new CharacterResource($this->service->find($model->getKey())))->response()->setStatusCode(201);
    }

    public function update(UpdateCharacterRequest $request, int $character): CharacterResource
    {
        $model = $this->service->update($this->service->find($character), $request->validated());

        return new CharacterResource($this->service->find($model->getKey()));
    }

    public function destroy(int $character): Response
    {
        $this->service->delete($this->service->find($character));

        return response()->noContent();
    }

}
