<?php

namespace App\Http\Controllers\Api;

use App\Http\Repositories\ProductRepository;
use App\Http\Requests\ProductUpdateRequest;
use App\Helpers\ResponseHelper;
use App\Http\Requests\ProductStoreRequest;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    private ProductRepository $productRepository;

    /**
     * @param  ProductRepository  $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $data = Product::with('categories:id,title')->paginate(12);
        return ProductResource::collection($data);
    }

    /**
     * @param ProductStoreRequest $request
     * @return ProductResource
     */
    public function store(ProductStoreRequest $request): ProductResource
    {
        $data = $request->validated();
        $product = $this->productRepository->store($data);
        return (new ProductResource($product))->additional(ResponseHelper::stored());
    }

    /**
     * @param Product $product
     * @return ProductResource
     */
    public function show(Product $product): ProductResource
    {
        $product = $this->productRepository->show($product);
        return new ProductResource($product);
    }

    /**
     * @param ProductUpdateRequest $request
     * @param Product $product
     * @return ProductResource
     */
    public function update(ProductUpdateRequest $request, Product $product): ProductResource
    {
        $data = $request->validated();
        $product = $this->productRepository->update($product, $data);
        return (new ProductResource($product))->additional(ResponseHelper::updated($product));
    }

    /**
     * @param Product $product
     * @return Response
     */
    public function destroy(Product $product): Response
    {
        $product->delete();
        return response()->noContent();
    }
}
