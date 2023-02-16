<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAllProducts(): Collection
    {
        return Product::all();
    }

    public function getProductById(int $productId): Eloquent|Builder|Product|null
    {
        return Product::where('id', $productId)->first();
    }

    public function deleteProduct(int $productId): int
    {
        return Product::destroy($productId);
    }

    public function createProduct(array $attributes): Eloquent|Product
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
