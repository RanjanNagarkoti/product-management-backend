<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetCategoriesController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function __invoke(): AnonymousResourceCollection
    {
        $categories = Category::select(['id', 'title'])->get();
        return CategoryResource::collection($categories);
    }
}
