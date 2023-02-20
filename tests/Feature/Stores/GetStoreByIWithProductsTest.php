<?php

declare(strict_types=1);

namespace Tests\Feature\Stores;

use App\Helpers\StoreMessageHelper;
use App\Helpers\StoresUrlHelper;
use App\Models\Product;
use App\Models\Store;
use App\Services\ProductService;
use App\Services\StoreService;
use Tests\TestCase;

class GetStoreByIWithProductsTest extends TestCase
{
    public function testGetStoreByIdWithProducts_withStoreCreatedWithoutProducts_returnsStoreWithEmptyProductsArray(
    ): void
    {
        $store = Store::factory()->create()::with(ProductService::KEY_PRODUCTS)->first();
        $endpoint = $this->getStoreByIdWithProductsEndpoint($store->id);

        $response = $this->getJson($endpoint)->assertOk()->json();

        $this->assertSuccessKeyTrue($response);
        $this->assertEquals($store->toArray(), $response[StoreService::KEY_STORE]);
    }

    public function testGetStoreByIdWithProducts_withStoreCreatedWithProducts_returnsStoreWithProducts(): void
    {
        $store = Store::factory()->create();
        $products = Product::factory()->count(5)->create();

        foreach ($products as $product) {
            $store->products()->attach($product->id);
        }

        $storeWithProducts = $store::with(ProductService::KEY_PRODUCTS)->first();
        $endpoint = $this->getStoreByIdWithProductsEndpoint($store->id);

        $response = $this->getJson($endpoint)->assertOk()->json();

        $this->assertSuccessKeyTrue($response);
        $this->assertEquals($storeWithProducts->toArray(), $response[StoreService::KEY_STORE]);
    }

    /** @dataProvider providesIdNotFound */
    public function testGetStoreByIdWithProducts_withNoStoreCreated_returnsStoreNotFound(int $storeIdNotFound): void
    {
        $endpoint = $this->getStoreByIdWithProductsEndpoint($storeIdNotFound);

        $response = $this->getJson($endpoint)->assertNotFound()->json();

        $this->assertSuccessKeyFalse($response);
        $this->assertMessageKey(StoreMessageHelper::STORE_NOT_FOUND, $response);
    }

    private function getStoreByIdWithProductsEndpoint(int $storeId): string
    {
        return $this->getEndpoint([StoresUrlHelper::STORES, $storeId, ProductService::KEY_PRODUCTS]);
    }
}

