<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Product;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{

    public function getAllProducts(): Collection;

    public function getProductById(int $productId): Eloquent|Builder|Product|null;

    public function deleteProduct(int $productId): int;

    public function createProduct(array $attributes): Eloquent|Product;

    public function updateProduct(int $productId, array $attributes): bool;

    public function saveProduct(Product $product): bool;
}
