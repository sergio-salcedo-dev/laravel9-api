<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Store;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface StoreRepositoryInterface
{
    public function findWithProducts(int $storeId): Eloquent|Builder|Store|null;

    public function allWithProducts(): Collection|array;

    public function allWithProductsCount(): Collection|array;

    /** @param int[] $productIds */
    public function detachProducts(Store $store, array $productIds): void;

    public function attachProduct(Store $store, int $productId, array $attributes): void;

    public function syncProduct(Store $store, Model|Collection|array $ids): void;
}
