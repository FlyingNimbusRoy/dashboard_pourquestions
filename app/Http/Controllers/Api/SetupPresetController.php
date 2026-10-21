<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SetupPresetResource;
use App\Services\Crud\SetupPresetService;
use Illuminate\Http\Request;
use App\Http\Requests\SetupPreset\StoreSetupPresetRequest;
use App\Http\Requests\SetupPreset\UpdateSetupPresetRequest;

class SetupPresetController extends Controller
{
    public function index(Request $request, SetupPresetService $service)
    {
        return SetupPresetResource::collection($service->list($request->query(), min((int) $request->query('per_page', 25), 200)));
    }

    public function show(SetupPresetService $service, int $id)
    {
        return new SetupPresetResource($service->find($id));
    }

    public function store(StoreSetupPresetRequest $request, SetupPresetService $service)
    {
        return (new SetupPresetResource($service->create($request->validated())))->response()->setStatusCode(201);
    }

    public function update(UpdateSetupPresetRequest $request, SetupPresetService $service, int $id)
    {
        return new SetupPresetResource($service->update($service->find($id), $request->validated()));
    }

    public function destroy(SetupPresetService $service, int $id)
    {
        $service->delete($service->find($id));

        return response()->noContent();
    }
}
