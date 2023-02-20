<?php

declare(strict_types=1);

namespace Tests\Feature\Products;

use App\Helpers\ProductMessageHelper;
use App\Helpers\ProductStoreMessageHelper;
use App\Helpers\ProductsUrlHelper;
use App\Helpers\StoreMessageHelper;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Tests\TestCase;

class SellProductTest extends TestCase
{
    public function testSellProduct_withInvalidRequest_returnsStatus422(): void
    {
        $request = $this->getRequest(0, -1);
        $endpoint = $this->getSellProductEndpoint();

        $this->postJson($endpoint, $request)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['storeId', 'productId'])
            ->json();
    }

    public function testSellProduct_withStoreNotFound_returnsStoreNotFound(): void
    {
        $request = $this->getRequest();
        $endpoint = $this->getSellProductEndpoint();

        $response = $this->postJson($endpoint, $request)->assertNotFound()->json();

        $this->assertSuccessKeyFalse($response);
        $this->assertMessageKey(StoreMessageHelper::STORE_NOT_FOUND, $response);
    }

    public function testSellProduct_withProductFound_returnsProductNotFound(): void
    {
        $store = Store::factory()->create();
        $request = $this->getRequest($store->id);
        $endpoint = $this->getSellProductEndpoint();

        $response = $this->postJson($endpoint, $request)->assertNotFound()->json();

        $this->assertSuccessKeyFalse($response);
        $this->assertMessageKey(ProductMessageHelper::PRODUCT_NOT_FOUND, $response);
    }

    public function testSellProduct_withoutPivot_doesNotSellProduct(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->create();
        $request = $this->getRequest($store->id, $product->id);
        $endpoint = $this->getSellProductEndpoint();

        $response = $this->postJson($endpoint, $request)->assertOk()->json();

        $this->assertSuccessKeyTrue($response);
        $this->assertMessageKey(ProductStoreMessageHelper::PRODUCT_OUT_STOCK, $response);
    }

    public function testSellProduct_withProductOutOfStock_doesNotSellProduct(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->create();
        $request = $this->getRequest($store->id, $product->id);
        $store->products()->attach($product->id, [ProductStore::STOCK => 0]);

        $endpoint = $this->getSellProductEndpoint();

        $response = $this->postJson($endpoint, $request)->assertOk()->json();

        $this->assertSuccessKeyTrue($response);
        $this->assertMessageKey(ProductStoreMessageHelper::PRODUCT_OUT_STOCK, $response);
    }

    public function testSellProduct_withOneProductInStock_sellsProductAndInformsStockIsOut(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->create();
        $request = $this->getRequest($store->id, $product->id);
        $store->products()->attach($product->id, [ProductStore::STOCK => 1]);
        $expectedMessage = ProductMessageHelper::PRODUCT_SOLD . ' ' . ProductStoreMessageHelper::STORE_RUN_OUT_STOCK;

        $endpoint = $this->getSellProductEndpoint();

        $response = $this->postJson($endpoint, $request)->assertOk()->json();

        $this->assertSuccessKeyTrue($response);
        $this->assertMessageKey($expectedMessage, $response);
        $this->assertEquals(0, $store->products->first()->pivot->stock);
    }

    public function testSellProduct_withProductStockLow_sellProductAndInformsStockIsRunningLow(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->create();
        $request = $this->getRequest($store->id, $product->id);
        $stock = ProductStore::STOCK_RUNNING_LOW_LIMIT;
        $store->products()->attach($product->id, [ProductStore::STOCK => ProductStore::STOCK_RUNNING_LOW_LIMIT]);
        $remainingStock = $stock - 1;
        $expectedMessage = ProductMessageHelper::PRODUCT_SOLD . ' ' . ProductStoreMessageHelper::STOCK_LOW . ", remaining: $remainingStock units";

        $endpoint = $this->getSellProductEndpoint();

        $response = $this->postJson($endpoint, $request)->assertOk()->json();

        $this->assertSuccessKeyTrue($response);
        $this->assertMessageKey($expectedMessage, $response);
        $this->assertEquals($remainingStock, $store->products->first()->pivot->stock);
    }

    public function testSellProduct_withProductInStock_sellsProduct(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->create();
        $request = $this->getRequest($store->id, $product->id);
        $stock = ProductStore::STOCK_RUNNING_LOW_LIMIT + 2;
        $store->products()->attach($product->id, [ProductStore::STOCK => $stock]);
        $remainingStock = $stock - 1;

        $endpoint = $this->getSellProductEndpoint();

        $response = $this->postJson($endpoint, $request)->assertOk()->json();

        $this->assertSuccessKeyTrue($response);
        $this->assertMessageKey(ProductMessageHelper::PRODUCT_SOLD, $response);
        $this->assertEquals($remainingStock, $store->products->first()->pivot->stock);
    }

    private function getSellProductEndpoint(): string
    {
        return $this->getEndpoint([ProductsUrlHelper::PRODUCTS, 'sell']);
    }

    private function getRequest(int $storeId = 1, int $productId = 1): array
    {
        return [
            'storeId' => $storeId,
            'productId' => $productId,
        ];
    }
}
