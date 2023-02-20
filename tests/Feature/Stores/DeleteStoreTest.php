<?php

declare(strict_types=1);

namespace Tests\Feature\Stores;

use App\Helpers\StoreMessageHelper;
use App\Helpers\StoresUrlHelper;
use App\Models\Store;
use App\Services\StoreService;
use Tests\TestCase;

class DeleteStoreTest extends TestCase
{
    public function testDeleteStore_withStoreFound_returnsStoreDeleted(): void
    {
        $store = Store::factory()
            ->create()
            ->makeVisible('deleted_at')
            ->makeHidden('updated_at')
            ->makeHidden('products');
        $endpoint = $this->getDeleteStoreEndpoint($store->id);

        $response = $this->deleteJson($endpoint)->assertOk()->json();

        $store->refresh();

        $this->assertSoftDeleted($store);
        $this->assertSuccessKeyTrue($response);
        $this->assertMessageKey(StoreMessageHelper::STORE_DELETED, $response);
        $this->assertEquals($store->attributesToArray(), $response[StoreService::KEY_STORE]);
    }

    /** @dataProvider providesIdNotFound */
    public function testDeleteStore_withStoreNotFound_returnsStoreNotFound(int $storeIdNotFound): void
    {
        $endpoint = $this->getDeleteStoreEndpoint($storeIdNotFound);

        $response = $this->deleteJson($endpoint)->assertNotFound()->json();

        $this->assertSuccessKeyFalse($response);
        $this->assertMessageKey(StoreMessageHelper::STORE_NOT_FOUND, $response);
    }

    private function getDeleteStoreEndpoint(int $storeId): string
    {
        return $this->getEndpoint([StoresUrlHelper::STORES, $storeId]);
    }
}
