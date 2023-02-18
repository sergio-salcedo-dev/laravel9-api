<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\PivotProductStoreRepositoryInterface;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PivotProductStoreRepository implements PivotProductStoreRepositoryInterface
{
    public function getPivotByIds(int $storeId, int $productId): Pivot|Builder|ProductStore|null
    {
        return ProductStore::where(['store_id' => $storeId, 'product_id' => $productId])->first();
    }

    public function getPivotByStoreAndProductId(Store $store, int $productId): Pivot|Builder|ProductStore|null
    {
        $product = $store->products->where('id', $productId)->first();

        return $product->pivot;
    }

    public function decrementStock(ProductStore $pivot, int $quantity = 1): bool|int
    {
        return $pivot->decrement('stock', $quantity);
    }

    public function incrementStock(ProductStore $pivot, int $quantity = 1): bool|int
    {
        return $pivot->increment('stock', $quantity);
    }
}
