<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ProductRepository implements ProductRepositoryInterface
{
    /** @return Model[]|Product[]|Collection */
    public function getAllProducts(): Collection|array
    {
        return Product::all();
    }

    public function getProductById(int $productId): Model|Store|null
    {
        return Product::where('id', $productId)->first();
    }

    public function deleteProduct(int $productId): int
    {
        return Product::destroy($productId);
    }

    public function createProduct(array $attributes): Product
    {
        return Product::create($attributes);
    }

    public function updateProduct(int $productId, array $attributes): bool
    {
        return $this->getProductById($productId)->update($attributes);
    }

    public function saveProduct(Product $product): bool
    {
        return $product->save();
    }
}
