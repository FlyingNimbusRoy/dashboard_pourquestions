<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\Crud\CategoryService;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->query('per_page', 25), 1), 200);

        return CategoryResource::collection($this->service->list($request->only(['search', 'sort', 'direction']), $perPage));
    }

    public function show(int $category): CategoryResource
    {
        return new CategoryResource($this->service->find($category));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $model = $this->service->create($request->validated());

        return (new CategoryResource($this->service->find($model->getKey())))->response()->setStatusCode(201);
    }

    public function update(UpdateCategoryRequest $request, int $category): CategoryResource
    {
        $model = $this->service->update($this->service->find($category), $request->validated());

        return new CategoryResource($this->service->find($model->getKey()));
    }

    public function destroy(int $category): Response
    {
        $this->service->delete($this->service->find($category));

        return response()->noContent();
    }

    /** POST api/categories/{category}/merge {target_id}: moves all questions into the target. */
    public function merge(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate(['target_id' => ['required', 'integer', 'exists:categories,id', 'different:'.$category->id]]);

        $moved = $this->service->merge($category, Category::findOrFail($data['target_id']));

        return response()->json(['moved' => $moved]);
    }
}
