<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Answer\StoreAnswerRequest;
use App\Http\Requests\Answer\UpdateAnswerRequest;
use App\Http\Resources\AnswerResource;
use App\Services\Crud\AnswerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AnswerController extends Controller
{
    public function __construct(private readonly AnswerService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->query('per_page', 25), 1), 200);

        return AnswerResource::collection($this->service->list($request->only(['search', 'sort', 'direction', 'question_id', 'is_correct']), $perPage));
    }

    public function show(int $answer): AnswerResource
    {
        return new AnswerResource($this->service->find($answer));
    }

    public function store(StoreAnswerRequest $request): JsonResponse
    {
        $model = $this->service->create($request->validated());

        return (new AnswerResource($this->service->find($model->getKey())))->response()->setStatusCode(201);
    }

    public function update(UpdateAnswerRequest $request, int $answer): AnswerResource
    {
        $model = $this->service->update($this->service->find($answer), $request->validated());

        return new AnswerResource($this->service->find($model->getKey()));
    }

    public function destroy(int $answer): Response
    {
        $this->service->delete($this->service->find($answer));

        return response()->noContent();
    }

}
