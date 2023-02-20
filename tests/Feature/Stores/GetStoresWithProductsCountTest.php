<?php

declare(strict_types=1);

namespace Tests\Feature\Stores;

use App\Helpers\StoresUrlHelper;
use App\Models\Product;
use App\Models\Store;
use App\Services\ProductService;
use App\Services\StoreService;
use Tests\TestCase;

class GetStoresWithProductsCountTest extends TestCase
{
    public function testGetStoresWithProductsCount_withNoStoresCreated_returnsNoStores(): void
    {
        $endpoint = $this->getStoresWithProductsCountEndpoint();

        $response = $this->getJson($endpoint)->assertOk()->json();

        $this->assertSuccessKeyTrue($response);
        $this->assertEmpty($response[StoreService::KEY_STORES]);
    }

    public function testGetStoresWithProductsCount_withStoreCreatedWithoutProducts_returnsStoreWithEmptyProductsArray(
    ): void
    {
        $store = Store::factory()->create()::withCount(ProductService::KEY_PRODUCTS)->first();
        $endpoint = $this->getStoresWithProductsCountEndpoint();

        $response = $this->getJson($endpoint)->assertOk()->json();

        $this->assertSuccessKeyTrue($response);
        $this->assertEquals($store->toArray(), $response[StoreService::KEY_STORES][0]);
    }

    public function testGetStoresWithProductsCount_withStoreCreatedWithProducts_returnsStoreWithProducts(): void
    {
        $stores = Store::factory()->count(2)->create();
        $products = Product::factory()->count(5)->create();

        $store1 = $stores[0];
        $store2 = $stores[1];

        // Attach 5 products to store1
        foreach ($products as $product) {
            $store1->products()->attach($product->id);
        }

        // Attach the 1st product to store2
        $store2->products()->attach($products->first()->id);

        $storeWithProductsCount1 = Store::withCount(ProductService::KEY_PRODUCTS)->where('id', $store1->id)->first();
        $storeWithProductsCount2 = Store::withCount(ProductService::KEY_PRODUCTS)->where('id', $store2->id)->first();

        $endpoint = $this->getStoresWithProductsCountEndpoint();

        $response = $this->getJson($endpoint)->assertOk()->json();

        $this->assertSuccessKeyTrue($response);
        $this->assertEquals($storeWithProductsCount1->toArray(), $response[StoreService::KEY_STORES][0]);
        $this->assertEquals($storeWithProductsCount2->toArray(), $response[StoreService::KEY_STORES][1]);
    }

    private function getStoresWithProductsCountEndpoint(): string
    {
        return $this->getEndpoint([StoresUrlHelper::STORES, 'products-count']);
    }
}

