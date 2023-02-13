<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\ProductStore;
use Eloquent;

interface ProductStoreRepositoryInterface
{
    public function getPivot(int $storeId, int $productId): Eloquent|ProductStore|null;
}
