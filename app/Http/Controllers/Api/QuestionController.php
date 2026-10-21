<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Question\StoreQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Services\Crud\QuestionService;
use App\Models\Question;
use App\Services\Questions\QuestionMover;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class QuestionController extends Controller
{
    public function __construct(private readonly QuestionService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->query('per_page', 25), 1), 200);

        return QuestionResource::collection($this->service->list($request->only(['search', 'sort', 'direction', 'category_id', 'uncategorized', 'gamepack_id', 'difficulty', 'is_nsfw', 'validation']), $perPage));
    }

    public function show(int $question): QuestionResource
    {
        return new QuestionResource($this->service->find($question));
    }

    public function store(StoreQuestionRequest $request): JsonResponse
    {
        $model = $this->service->create($request->validated());

        return (new QuestionResource($this->service->find($model->getKey())))->response()->setStatusCode(201);
    }

    public function update(UpdateQuestionRequest $request, int $question): QuestionResource
    {
        $model = $this->service->update($this->service->find($question), $request->validated());

        return new QuestionResource($this->service->find($model->getKey()));
    }

    public function destroy(int $question): Response
    {
        $this->service->delete($this->service->find($question));

        return response()->noContent();
    }

    /** POST api/questions/move-category {question_ids[], category_id|null} */
    public function moveCategory(Request $request, QuestionMover $mover): JsonResponse
    {
        $data = $request->validate([
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:questions,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $moved = $mover->moveToCategory(Question::whereIn('id', $data['question_ids'])->get(), $data['category_id'] ?? null);

        return response()->json(['moved' => $moved]);
    }
}
