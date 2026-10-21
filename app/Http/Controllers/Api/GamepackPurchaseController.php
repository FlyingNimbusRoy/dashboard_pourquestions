<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GamepackPurchaseResource;
use App\Services\Crud\GamepackPurchaseService;
use Illuminate\Http\Request;
use App\Http\Requests\GamepackPurchase\StoreGamepackPurchaseRequest;

class GamepackPurchaseController extends Controller
{
    public function index(Request $request, GamepackPurchaseService $service)
    {
        return GamepackPurchaseResource::collection($service->list($request->query(), min((int) $request->query('per_page', 25), 200)));
    }

    public function show(GamepackPurchaseService $service, int $id)
    {
        return new GamepackPurchaseResource($service->find($id));
    }

    public function store(StoreGamepackPurchaseRequest $request, GamepackPurchaseService $service)
    {
        return (new GamepackPurchaseResource($service->create($request->validated())))->response()->setStatusCode(201);
    }

    public function destroy(GamepackPurchaseService $service, int $id)
    {
        $service->delete($service->find($id));

        return response()->noContent();
    }
}
