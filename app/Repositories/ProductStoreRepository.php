<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\ProductStoreRepositoryInterface;
use App\Models\ProductStore;
use Eloquent;

class ProductStoreRepository implements ProductStoreRepositoryInterface
{
    public function getPivot(int $storeId, int $productId): ProductStore|Eloquent|null
    {
        return ProductStore::where(['store_id' => $storeId, 'product_id' => $productId])->first();
    }
}
