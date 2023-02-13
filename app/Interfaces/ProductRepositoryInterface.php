<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ProductRepositoryInterface
{
    /** @return Model[]|Product[]|Collection */
    public function getAllProducts(): Collection|array;

    public function getProductById(int $productId): Model|Store|null;

    public function deleteProduct(int $productId): int;

    public function createProduct(array $attributes): Product;

    public function updateProduct(int $productId, array $attributes): bool;
}
