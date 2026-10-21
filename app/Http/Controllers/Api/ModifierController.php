<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modifier\StoreModifierRequest;
use App\Http\Requests\Modifier\UpdateModifierRequest;
use App\Http\Resources\ModifierResource;
use App\Services\Crud\ModifierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ModifierController extends Controller
{
    public function __construct(private readonly ModifierService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->query('per_page', 25), 1), 200);

        return ModifierResource::collection($this->service->list($request->only(['search', 'sort', 'direction', 'gamepack_id', 'is_active', 'turnbased']), $perPage));
    }

    public function show(int $modifier): ModifierResource
    {
        return new ModifierResource($this->service->find($modifier));
    }

    public function store(StoreModifierRequest $request): JsonResponse
    {
        $model = $this->service->create($request->validated());

        return (new ModifierResource($this->service->find($model->getKey())))->response()->setStatusCode(201);
    }

    public function update(UpdateModifierRequest $request, int $modifier): ModifierResource
    {
        $model = $this->service->update($this->service->find($modifier), $request->validated());

        return new ModifierResource($this->service->find($model->getKey()));
    }

    public function destroy(int $modifier): Response
    {
        $this->service->delete($this->service->find($modifier));

        return response()->noContent();
    }

}
