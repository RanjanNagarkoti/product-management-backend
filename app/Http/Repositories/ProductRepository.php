<?php

namespace App\Http\Repositories;

use Illuminate\Support\Facades\Storage;
use App\Models\Product;

class ProductRepository
{
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
            Storage::delete('app/images/products/' . $product->thumbnail);
            $name = date('ymd') . time() . '.' . $data['thumbnail']->extension();
            $data['thumbnail'] = $data['thumbnail']->storeAs('images/products', $name);
        }
        $product->categories()->sync($data['categories']);
        $product->update($data);

        return $product;
    }
}
