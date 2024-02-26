<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Requests\CategoryStoreRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $data = Category::paginate(10);
        return CategoryResource::collection($data);
    }

    /**
     * @param CategoryStoreRequest $request
     * @return CategoryResource
     */
    public function store(CategoryStoreRequest $request): CategoryResource
    {
        $data = $request->validated();
        $category = Category::create($data);
        return (new CategoryResource($category))->additional(ResponseHelper::stored());
    }

    /**
     * @param Category $Category
     * @return CategoryResource
     */
    public function show(Category $Category): CategoryResource
    {
        return new CategoryResource($Category);
    }

    /**
     * @param CategoryUpdateRequest $request
     * @param Category $Category
     * @return CategoryResource
     */
    public function update(CategoryUpdateRequest $request, Category $Category): CategoryResource
    {
        $data = $request->validated();

        $Category->update($data);

        return (new CategoryResource($Category))->additional(ResponseHelper::updated($Category));
    }

    /**
     * @param Category $Category
     * @return Response
     */
    public function destroy(Category $Category): Response
    {
        $Category->delete();
        return response()->noContent();
    }
}
