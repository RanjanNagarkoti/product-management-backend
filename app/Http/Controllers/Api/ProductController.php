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
use App\Http\Requests\ProductFilterRequest;
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
     * @param ProductFilterRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(ProductFilterRequest $request): AnonymousResourceCollection
    {
        $data = $request->validated();
        $product = $this->productRepository->getFilteredProducts($data);
        return ProductResource::collection($product);
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
        return $this->productRepository->destroy($product);
    }
}
