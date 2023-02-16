<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

interface PivotProductStoreRepositoryInterface
{
    public function getPivotByIds(int $storeId, int $productId): Pivot|Builder|ProductStore|null;

    public function getPivotByStoreAndProductId(Store $store, int $productId): Pivot|Builder|ProductStore|null;

    public function decrementStock(ProductStore $pivot, int $quantity = 1): bool|int;

    public function incrementStock(ProductStore $pivot, int $quantity = 1): bool|int;
}
