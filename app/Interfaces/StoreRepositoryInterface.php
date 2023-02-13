<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Store;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface StoreRepositoryInterface
{
    /** @return Model[]|Store[]|Collection */
    public function getAllStores(): Collection|array;

    public function getStoreById(int $storeId): Model|Store|null;

    public function getStoreWithProducts(int $storeId): Collection|Model|Store|null;

    /** @return Model[]|Store[]|Collection */
    public function getStoresWithProducts(): Collection|array;

    /** @return Model[]|Store[]|Collection */
    public function getStoresWithProductsCount(): Collection|array;

    public function createStore(array $attributes): Model|Store;

    public function updateStore(int $storeId, array $attributes): bool;

    public function deleteStore(int $storeId): int;

    /** @param int[] $productIds */
    public function detachProductsFromStore(Store $store, array $productIds): void;

    public function attachProductToStore(Store $store, int $productId, array $attributes): void;

    public function syncProducts(Store $store, Model|Collection|array $ids): void;
}
