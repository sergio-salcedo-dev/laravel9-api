<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;

class ProductRepository extends EloquentModelRepository implements ProductRepositoryInterface
{
    protected function modelClass(): string|Product
    {
        return Product::class;
    }
}
