<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoozeBuddyResource;
use App\Services\Crud\BoozeBuddyService;
use Illuminate\Http\Request;
use App\Http\Requests\BoozeBuddy\StoreBoozeBuddyRequest;
use App\Http\Requests\BoozeBuddy\UpdateBoozeBuddyRequest;

class BoozeBuddyController extends Controller
{
    public function index(Request $request, BoozeBuddyService $service)
    {
        return BoozeBuddyResource::collection($service->list($request->query(), min((int) $request->query('per_page', 25), 200)));
    }

    public function show(BoozeBuddyService $service, int $id)
    {
        return new BoozeBuddyResource($service->find($id));
    }

    public function store(StoreBoozeBuddyRequest $request, BoozeBuddyService $service)
    {
        return (new BoozeBuddyResource($service->create($request->validated())))->response()->setStatusCode(201);
    }

    public function update(UpdateBoozeBuddyRequest $request, BoozeBuddyService $service, int $id)
    {
        return new BoozeBuddyResource($service->update($service->find($id), $request->validated()));
    }

    public function destroy(BoozeBuddyService $service, int $id)
    {
        $service->delete($service->find($id));

        return response()->noContent();
    }
}
