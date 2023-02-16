<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\StoreRepositoryInterface;
use App\Models\Store;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class StoreRepository implements StoreRepositoryInterface
{
    public function getAllStores(): Collection
    {
        return Store::all();
    }

    public function getStoreById(int $storeId): Eloquent|Builder|Store|null
    {
        return Store::where('id', $storeId)->first();
    }

    public function getStoreWithProducts(int $storeId): Eloquent|Builder|Store|null
    {
        return Store::with('products')->where('id', $storeId)->first();
    }

    /** @return Store[]|Collection */
    public function getStoresWithProductsCount(): Collection|array
    {
        return Store::withCount('products')->get();
    }

    /** @return Store[]|Collection */
    public function getStoresWithProducts(): Collection|array
    {
        return Store::with('products')->get();
    }

    public function deleteStore(int $storeId): int
    {
        return Store::destroy($storeId);
    }

    public function createStore(array $attributes): Eloquent|Store
    {
        return Store::create($attributes);
    }

    public function updateStore(int $storeId, array $attributes): bool|int
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
