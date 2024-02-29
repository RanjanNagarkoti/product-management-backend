<?php

namespace App\Http\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use Illuminate\Http\Response;

class ProductRepository
{

    /**
     *
     * @param array $data, $export
     * @return LengthAwarePaginator|Collection
     */
    public function getFilteredProducts(array $data): LengthAwarePaginator|Collection
    {
        $query = Product::with('categories:id,title');

        if (isset($data['name'])) {
            $query->where('name', 'like', '%' . $data['name'] . '%');
        }

        if (isset($data['sort'])) {
            $query->orderBy('price', $data['sort']);
        }

        if (isset($data['category_id'])) {
            $categoryIds = is_array($data['category_id']) ? $data['category_id'] : explode(',', $data['category_id']);
            $query->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('category_product.category_id', $categoryIds);
            });
        }

        return $query->paginate(12);
    }



    /**
     * @param array $data
     * @return Product
     */
    public function store(array $data): Product
    {
        $name = date('ymd') . time() . '.' . $data['thumbnail']->extension();
        $data['thumbnail'] = $data['thumbnail']->storeAs('images/products', $name);
        $product = product::create($data);
        $product->categories()->sync($data['categories']);
        return $product->fresh();
    }


    /**
     * @param Product $product
     * @return Product
     */
    public function show(Product $product): product
    {
        return $product->load(['categories:id,title']);
    }


    /**
     * @param Product $product
     * @param array $data
     * @return Product
     */
    public function update(Product $product, array $data): Product
    {
        if (isset($data['thumbnail'])) {
            unlink(storage_path('app/' . $product->thumbnail));
            $name = date('ymd') . time() . '.' . $data['thumbnail']->extension();
            $data['thumbnail'] = $data['thumbnail']->storeAs('images/products', $name);
        }
        $product->categories()->sync($data['categories']);
        $product->update($data);

        return $product;
    }


    /**
     * @param Product $product
     * @return Response
     */
    public function destroy(Product $product): Response
    {
        unlink(storage_path('app/' . $product->thumbnail));
        $product->delete();
        return response()->noContent();
    }
}
