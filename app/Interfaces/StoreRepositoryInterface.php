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
    public function getAllStores(): Collection;

    public function getStoreById(int $storeId): Eloquent|Builder|Store|null;

    public function getStoreWithProducts(int $storeId): Eloquent|Builder|Store|null;

    public function getStoresWithProducts(): Collection|array;

    public function getStoresWithProductsCount(): Collection|array;

    public function createStore(array $attributes): Eloquent|Store;

    public function updateStore(int $storeId, array $attributes): bool|int;

    public function saveStore(Store $store): bool;

    public function deleteStore(int $storeId): int;

    /** @param int[] $productIds */
    public function detachProductsFromStore(Store $store, array $productIds): void;

    public function attachProductToStore(Store $store, int $productId, array $attributes): void;

    public function syncProducts(Store $store, Model|Collection|array $ids): void;
}
