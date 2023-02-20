<?php

declare(strict_types=1);

namespace Tests\Feature\Stores;

use App\Helpers\StoreMessageHelper;
use App\Helpers\StoresUrlHelper;
use App\Models\Product;
use App\Models\Store;
use App\Services\StoreService;
use Tests\TestCase;

class CreateStoreTest extends TestCase
{
    public function testCreateStore_withNoStoreName_returnsStatus422(): void
    {
        $invalidDataInRequest = $this->getRequest();

        $this->postJson(StoresUrlHelper::STORES, $invalidDataInRequest)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name'])
            ->json();
    }

    public function testCreateStore_withProductIdsNoIntegersGreaterThanCero_returnsStatus422(): void
    {
        $invalidDataInRequest = $this->getRequest('New Test Store', [0, -1]);

        $this->postJson(StoresUrlHelper::STORES, $invalidDataInRequest)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['productIds.0', 'productIds.1'])
            ->json();
    }

    public function testCreateStore_withProductIdsNonNumeric_returnsStatus422(): void
    {
        $invalidDataInRequest = $this->getRequest('New Test Store', ['foo', 'bar']);

        $this->postJson(StoresUrlHelper::STORES, $invalidDataInRequest)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['productIds.0', 'productIds.1'])
            ->json();
    }

    public function testCreateStore_withTooManyProductIds_returnsStatus422(): void
    {
        $invalidDataInRequest = $this->getRequest('New Test Store', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]);

        $this->postJson(StoresUrlHelper::STORES, $invalidDataInRequest)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['productIds'])
            ->json();
    }

    public function testCreateStore_withNameAndEmptyArrayOfProductIds_returnsStatus422(): void
    {
        $invalidDataInRequest = $this->getRequest('New Test Store', []);

        $this->postJson(StoresUrlHelper::STORES, $invalidDataInRequest)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['productIds'])
            ->json();
    }

    public function testCreateStore_testCreateStore_withInvalidProductIdInProducts_returnsStatus422(): void
    {
        $invalidDataInRequest = $this->getRequest('New Test Store', null, [['id' => 'foo', 'stock' => 5]]);

        $this->postJson(StoresUrlHelper::STORES, $invalidDataInRequest)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['products.0.id'])
            ->json();
    }

    public function testCreateStore_testCreateStore_withNegativeStock_returnsStatus422(): void
    {
        $invalidDataInRequest = $this->getRequest('New Test Store', null, [['id' => 1, 'stock' => -5]]);

        $this->postJson(StoresUrlHelper::STORES, $invalidDataInRequest)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['products.0.stock'])
            ->json();
    }

    public function testCreateStore_withEmptyArrayOfProducts_createsStoreAndReturnsStatus201(): void
    {
        $request = $this->getRequest('New Test Store', null, []);

        $response = $this->postJson(StoresUrlHelper::STORES, $request)
            ->assertCreated()
            ->json();
        $actualStore = $response[StoreService::KEY_STORE];
        $newStore = Store::with('products')
            ->where('id', $actualStore['id'])
            ->first()
            ->makeHidden('updated_at');

        $this->assertMessageKey(StoreMessageHelper::STORE_CREATED, $response);
        $this->assertEquals($newStore->toArray(), $actualStore);
    }

    public function testCreateStore_withName_createsStoreAndReturnsStatus201(): void
    {
        $request = $this->getRequest('New Test Store');

        $response = $this->postJson(StoresUrlHelper::STORES, $request)
            ->assertCreated()
            ->json();
        $actualStore = $response[StoreService::KEY_STORE];
        $newStore = Store::with('products')
            ->where('id', $actualStore['id'])
            ->first()
            ->makeHidden('updated_at');

        $this->assertMessageKey(StoreMessageHelper::STORE_CREATED, $response);
        $this->assertEquals($newStore->toArray(), $actualStore);
    }

    public function testCreateStore_withNameAndProductIds_createsStoreWithProductsAndReturnsStatus201(): void
    {
        Product::factory()->count(3)->create();
        $request = $this->getRequest('New Test Store', $this->getValidProductIdsKeyData());

        $response = $this->postJson(StoresUrlHelper::STORES, $request)
            ->assertCreated()
            ->json();
        $actualStore = $response[StoreService::KEY_STORE];
        $newStore = Store::with('products')
            ->where('id', $actualStore['id'])
            ->first()
            ->makeHidden('updated_at');

        $this->assertMessageKey(StoreMessageHelper::STORE_CREATED, $response);
        $this->assertEquals($newStore->toArray(), $actualStore);
    }

    public function testCreateStore_withNameAndAllProductsFound_createsStoreWithProductsAndReturnsStatus201(): void
    {
        Product::factory()->count(4)->create();
        $request = $this->getRequest('New Test Store', null, $this->getValidProductsKeyData());

        $response = $this->postJson(StoresUrlHelper::STORES, $request)
            ->assertCreated()
            ->json();
        $actualStore = $response[StoreService::KEY_STORE];
        $newStore = Store::with('products')
            ->where('id', $actualStore['id'])
            ->first()
            ->makeHidden('updated_at');

        $this->assertMessageKey(StoreMessageHelper::STORE_CREATED, $response);
        $this->assertEquals($newStore->toArray(), $actualStore);
    }

    public function testCreateStore_withNameAndTwoProductsFound_createsStoreWithProductsAndReturnsStatus201(): void
    {
        Product::factory()->count(2)->create();
        $request = $this->getRequest('New Test Store', null, $this->getValidProductsKeyData());

        $response = $this->postJson(StoresUrlHelper::STORES, $request)
            ->assertCreated()
            ->json();
        $actualStore = $response[StoreService::KEY_STORE];
        $newStore = Store::with('products')
            ->where('id', $actualStore['id'])
            ->first()
            ->makeHidden('updated_at');

        $this->assertMessageKey(StoreMessageHelper::STORE_CREATED, $response);
        $this->assertEquals($newStore->toArray(), $actualStore);
    }

    public function testCreateStore_withNameAndMixedProductDataWithAllProductsFound_createsStoreWithProductsAndReturnsStatus201(
    ): void
    {
        Product::factory()->count(4)->create();
        $request = $this->getValidMixedData();

        $response = $this->postJson(StoresUrlHelper::STORES, $request)
            ->assertCreated()
            ->json();
        $actualStore = $response[StoreService::KEY_STORE];
        $newStore = Store::with('products')
            ->where('id', $actualStore['id'])
            ->first()
            ->makeHidden('updated_at');

        $this->assertMessageKey(StoreMessageHelper::STORE_CREATED, $response);
        $this->assertEquals($newStore->toArray(), $actualStore);
    }

    public function testCreateStore_withNameAndMixedAndRepeatedProductData_createsStoreWithProductsAndReturnsStatus201(
    ): void
    {
        Product::factory()->count(4)->create();
        $request = $this->getValidMixedAndRepeatedData();

        $response = $this->postJson(StoresUrlHelper::STORES, $request)
            ->assertCreated()
            ->json();
        $actualStore = $response[StoreService::KEY_STORE];
        $newStore = Store::with('products')
            ->where('id', $actualStore['id'])
            ->first()
            ->makeHidden('updated_at');

        $this->assertMessageKey(StoreMessageHelper::STORE_CREATED, $response);
        $this->assertEquals($newStore->toArray(), $actualStore);
    }

    /**
     * @param int[] $productIds
     * @param array[] $products
     */
    private function getRequest(string $name = '', ?array $productIds = null, ?array $products = null): array
    {
        return [
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
