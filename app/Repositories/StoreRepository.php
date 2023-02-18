<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\StoreRepositoryInterface;
use App\Models\Store;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class StoreRepository extends EloquentModelRepository implements StoreRepositoryInterface
{
    protected function modelClass(): string|Store
    {
        return Store::class;
    }

    public function findWithProducts(int $storeId): Eloquent|Builder|Store|null
    {
        return $this->modelClass()::with('products')->where('id', $storeId)->first();
    }

    /** @return Store[]|Collection */
    public function allWithProductsCount(): Collection|array
    {
        return $this->modelClass()::withCount('products')->get();
    }

    /** @return Store[]|Collection */
    public function allWithProducts(): Collection|array
    {
        return $this->modelClass()::with('products')->get();
    }

    public function detachProducts(Store $store, array $productIds): void
    {
        $store->products()->detach($productIds);
    }

    public function attachProduct(Store $store, int $productId, array $attributes = []): void
    {
        $store->products()->attach($productId, $attributes);
    }

    public function syncProduct(Store $store, Model|Collection|array $ids): void
    {
        $store->products()->sync($ids);
    }
}
