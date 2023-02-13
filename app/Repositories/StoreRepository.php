<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\StoreRepositoryInterface;
use App\Models\Store;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class StoreRepository implements StoreRepositoryInterface
{
    /** @return Model[]|Store[]|Collection */
    public function getAllStores(): Collection|array
    {
        return Store::all();
    }

    public function getStoreById(int $storeId): Model|Store|null
    {
        return Store::where('id', $storeId)->first();
    }

    public function getStoreWithProducts(int $storeId): Collection|Model|Store|null
    {
        return Store::with('products')->where('id', $storeId)->get();
    }

    /** @return Model[]|Store[]|Collection */
    public function getStoresWithProductsCount(): Collection|array
    {
        return Store::withCount('products')->get();
    }

    public function getStoresWithProducts(): array
    {
        return Store::with('products')->get();
    }

    public function deleteStore(int $storeId): int
    {
        return Store::destroy($storeId);
    }

    public function createStore(array $attributes): Model|Store
    {
        return Store::create($attributes);
    }

    public function updateStore(int $storeId, array $attributes): bool
    {
        return $this->getStoreById($storeId)->update($attributes);
    }

    public function saveStore(Store $store): bool
    {
        return $store->save();
    }

    public function detachProductsFromStore(Store $store, array $productIds): void
    {
        $store->products()->detach($productIds);
    }

    public function attachProductToStore(Store $store, int $productId, array $attributes = []): void
    {
        $store->products()->attach($productId, $attributes);
    }

    public function syncProducts(Store $store, Model|Collection|array $ids): void
    {
        foreach ($ids as $id) {
            $store->products()->sync($id);
        }
    }
}
