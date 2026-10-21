<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Services\Crud\CommentService;
use Illuminate\Http\Request;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;

class CommentController extends Controller
{
    public function index(Request $request, CommentService $service)
    {
        return CommentResource::collection($service->list($request->query(), min((int) $request->query('per_page', 25), 200)));
    }

    public function show(CommentService $service, int $id)
    {
        return new CommentResource($service->find($id));
    }

    public function store(StoreCommentRequest $request, CommentService $service)
    {
        return (new CommentResource($service->create($request->validated())))->response()->setStatusCode(201);
    }

    public function update(UpdateCommentRequest $request, CommentService $service, int $id)
    {
        return new CommentResource($service->update($service->find($id), $request->validated()));
    }

    public function destroy(CommentService $service, int $id)
    {
        $service->delete($service->find($id));

        return response()->noContent();
    }
}
