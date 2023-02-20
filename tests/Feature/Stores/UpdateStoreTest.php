<?php

declare(strict_types=1);

namespace Tests\Feature\Stores;

use App\Helpers\StoreMessageHelper;
use App\Helpers\StoresUrlHelper;
use App\Models\Product;
use App\Models\Store;
use App\Services\StoreService;
use Tests\TestCase;

class UpdateStoreTest extends TestCase
{

    public function testUpdateStore_withStoreNotFound_returnsStoreNotFound(): void
    {
        $request = $this->getRequest();

        $response = $this->putJson($this->getUpdateStoreEndpoint(1), $request)->assertNotFound()->json();

        $this->assertSuccessKeyFalse($response);
        $this->assertMessageKey(StoreMessageHelper::STORE_NOT_FOUND, $response);
    }

    public function testUpdateStore_withNoStoreNameKeepStoreName_returnsStatus422(): void
    {
        $store = Store::factory()->create();
        $request = $this->getRequest();

        $response = $this->putJson($this->getUpdateStoreEndpoint($store->id), $request)
            ->assertOk()
            ->json();
        $actualStore = $response[StoreService::KEY_STORE];
        $newStore = Store::with('products')
            ->where('id', $actualStore['id'])
            ->first();

        $this->assertMessageKey(StoreMessageHelper::STORE_NOT_UPDATED, $response);
        $this->assertEquals($newStore->toArray(), $actualStore);
    }

    public function testUpdateStore_withProductIdsNoIntegersGreaterThanCero_returnsStatus422(): void
    {
        $store = Store::factory()->create();
        $invalidDataInRequest = $this->getRequest($store->id, 'Updated Test Store', [0, -1]);

        $this->putJson($this->getUpdateStoreEndpoint($store->id), $invalidDataInRequest)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['productIds.0', 'productIds.1'])
            ->json();
    }

    public function testUpdateStore_withProductIdsNonNumeric_returnsStatus422(): void
    {
        $store = Store::factory()->create();
        $invalidDataInRequest = $this->getRequest($store->id, 'Updated Test Store', ['foo', 'bar']);

        $this->putJson($this->getUpdateStoreEndpoint($store->id), $invalidDataInRequest)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['productIds.0', 'productIds.1'])
            ->json();
    }

    public function testUpdateStore_withTooManyProductIds_returnsStatus422(): void
    {
        $store = Store::factory()->create();
        $invalidDataInRequest = $this->getRequest($store->id, 'Updated Test Store', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
        );

        $this->putJson($this->getUpdateStoreEndpoint($store->id), $invalidDataInRequest)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['productIds'])
            ->json();
    }

    public function testUpdateStore_withNameAndEmptyArrayOfProductIds_returnsStatus422(): void
    {
        $store = Store::factory()->create();
        $invalidDataInRequest = $this->getRequest($store->id, 'Updated Test Store', []);

        $this->putJson($this->getUpdateStoreEndpoint($store->id), $invalidDataInRequest)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['productIds'])
            ->json();
    }

    public function testUpdateStore_testUpdateStore_withInvalidProductIdInProducts_returnsStatus422(): void
    {
        $store = Store::factory()->create();
        $invalidDataInRequest = $this->getRequest(
            $store->id,
            'Updated Test Store',
            null,
            [['id' => 'foo', 'stock' => 5]]
        );

        $this->putJson($this->getUpdateStoreEndpoint($store->id), $invalidDataInRequest)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['products.0.id'])
            ->json();
    }

    public function testUpdateStore_testUpdateStore_withNegativeStock_returnsStatus422(): void
    {
        $store = Store::factory()->create();
        $invalidDataInRequest = $this->getRequest($store->id, 'Updated Test Store', null, [['id' => 1, 'stock' => -5]]);

        $this->putJson($this->getUpdateStoreEndpoint($store->id), $invalidDataInRequest)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['products.0.stock'])
            ->json();
    }

    public function testUpdateStore_withEmptyArrayOfProducts_updatesStoreAndReturnsStatus200(): void
    {
        $store = Store::factory()->create();
        $request = $this->getRequest($store->id, 'Updated Test Store', null, []);

        $response = $this->putJson($this->getUpdateStoreEndpoint($store->id), $request)
            ->assertOk()
            ->json();
        $actualStore = $response[StoreService::KEY_STORE];
        $newStore = Store::with('products')
            ->where('id', $actualStore['id'])
            ->first();

        $this->assertMessageKey(StoreMessageHelper::STORE_UPDATED, $response);
        $this->assertEquals($newStore->toArray(), $actualStore);
    }

    public function testUpdateStore_withName_createsStoreAndReturnsStatus200(): void
    {
        $store = Store::factory()->create();
        $request = $this->getRequest($store->id, 'Updated Test Store');

        $response = $this->putJson($this->getUpdateStoreEndpoint($store->id), $request)
            ->assertOk()
            ->json();
        $actualStore = $response[StoreService::KEY_STORE];
        $newStore = Store::with('products')
            ->where('id', $actualStore['id'])
            ->first();

        $this->assertMessageKey(StoreMessageHelper::STORE_UPDATED, $response);
        $this->assertEquals($newStore->toArray(), $actualStore);
    }

    public function testUpdateStore_withNameAndProductIds_createsStoreWithProductsAndReturnsStatus200(): void
    {
        $store = Store::factory()->create();
        Product::factory()->count(3)->create();
        $request = $this->getRequest($store->id, 'Updated Test Store', $this->getValidProductIdsKeyData());

        $response = $this->putJson($this->getUpdateStoreEndpoint($store->id), $request)
            ->assertOk()
            ->json();
        $actualStore = $response[StoreService::KEY_STORE];
        $newStore = Store::with('products')
            ->where('id', $actualStore['id'])
            ->first();

        $this->assertMessageKey(StoreMessageHelper::STORE_UPDATED, $response);
        $this->assertEquals($newStore->toArray(), $actualStore);
    }

    public function testUpdateStore_withNameAndAllProductsFound_createsStoreWithProductsAndReturnsStatus200(): void
    {
        $store = Store::factory()->create();
        Product::factory()->count(4)->create();
        $request = $this->getRequest($store->id, 'Updated Test Store', null, $this->getValidProductsKeyData());

        $response = $this->putJson($this->getUpdateStoreEndpoint($store->id), $request)
            ->assertOk()
            ->json();
        $actualStore = $response[StoreService::KEY_STORE];
        $newStore = Store::with('products')
            ->where('id', $actualStore['id'])
            ->first();

        $this->assertMessageKey(StoreMessageHelper::STORE_UPDATED, $response);
        $this->assertEquals($newStore->toArray(), $actualStore);
    }

    public function testUpdateStore_withNameAndTwoProductsFound_createsStoreWithProductsAndReturnsStatus200(): void
    {
        $store = Store::factory()->create();
        Product::factory()->count(2)->create();
        $request = $this->getRequest($store->id, 'Updated Test Store', null, $this->getValidProductsKeyData());

        $response = $this->putJson($this->getUpdateStoreEndpoint($store->id), $request)
            ->assertOk()
            ->json();
        $actualStore = $response[StoreService::KEY_STORE];
        $newStore = Store::with('products')
            ->where('id', $actualStore['id'])
            ->first();

        $this->assertMessageKey(StoreMessageHelper::STORE_UPDATED, $response);
        $this->assertEquals($newStore->toArray(), $actualStore);
    }

    public function testUpdateStore_withNameAndMixedProductDataWithAllProductsFound_createsStoreWithProductsAndReturnsStatus200(
    ): void
    {
        $store = Store::factory()->create();
        Product::factory()->count(4)->create();
        $request = $this->getValidMixedData();

        $response = $this->putJson($this->getUpdateStoreEndpoint($store->id), $request)
            ->assertOk()
            ->json();
        $actualStore = $response[StoreService::KEY_STORE];
        $newStore = Store::with('products')
            ->where('id', $actualStore['id'])
            ->first();

        $this->assertMessageKey(StoreMessageHelper::STORE_UPDATED, $response);
        $this->assertEquals($newStore->toArray(), $actualStore);
    }

    public function testUpdateStore_withNameAndMixedAndRepeatedProductData_createsStoreWithProductsAndReturnsStatus200(
    ): void
    {
        $store = Store::factory()->create();
        Product::factory()->count(4)->create();
        $request = $this->getValidMixedAndRepeatedData();

        $response = $this->putJson($this->getUpdateStoreEndpoint($store->id), $request)
            ->assertOk()
            ->json();
        $actualStore = $response[StoreService::KEY_STORE];
        $newStore = Store::with('products')
            ->where('id', $actualStore['id'])
            ->first();

        $this->assertMessageKey(StoreMessageHelper::STORE_UPDATED, $response);
        $this->assertEquals($newStore->toArray(), $actualStore);
    }

    private function getUpdateStoreEndpoint(int $storeId): string
    {
        return $this->getEndpoint([StoresUrlHelper::STORES, $storeId]);
    }

    /**
     * @param int[] $productIds
     * @param array[] $products
     */
    private function getRequest(
        int $storeId = 1,
        string $name = '',
        ?array $productIds = null,
        ?array $products = null
    ): array {
        return [
            '$storeId' => $storeId,
            'name' => $name,
            'productIds' => $productIds,
            'products' => $products,
        ];
    }

    private function getValidMixedData(): array
    {
        return [
            'name' => 'Test Store',
            'productIds' => $this->getValidProductIdsKeyData(),
            'products' => $this->getValidProductsKeyData(),
        ];
    }

    private function getValidProductsKeyData(): array
    {
        return [
            ['id' => 1, 'stock' => 5],
            ['id' => 2, 'stock' => 10],
            ['id' => 3, 'stock' => null],
            ['id' => 4],
        ];
    }

    private function getValidProductIdsKeyData(): array
    {
        return [1, 2, 3];
    }

    private function getValidMixedAndRepeatedData(): array
    {
        return [
            'name' => 'Test Store',
            'productIds' => $this->getValidProductIdsKeyData(),
            'products' => [
                ['id' => 1, 'stock' => 5],
                ['id' => 1, 'stock' => 10],
                ['id' => 2, 'stock' => 2],
                ['id' => 2],
                ['id' => 3, 'stock' => null],
                ['id' => 3],
                ['id' => 4, 'stock' => 0],
                ['id' => 4],
            ],
        ];
    }
}
