<?php

declare(strict_types=1);

namespace Tests\Feature\Stores;

use App\Helpers\StoreMessageHelper;
use App\Helpers\StoresUrlHelper;
use App\Models\Store;
use App\Services\StoreService;
use Tests\TestCase;

class GetStoreTest extends TestCase
{
    public function testGetStore_withStoreCreated_returnsStore(): void
    {
        $store = Store::factory()->create();
        $endpoint = $this->getStoreEndpoint($store->id);

        $response = $this->getJson($endpoint)->assertOk()->json();

        $this->assertSuccessKeyTrue($response);
        $this->assertEquals($store->attributesToArray(), $response[StoreService::KEY_STORE]);
    }

    /** @dataProvider providesIdNotFound */
    public function testGetStore_withNoStoreCreated_returnsStoreNotFound(int $storeIdNotFound): void
    {
        $endpoint = $this->getStoreEndpoint($storeIdNotFound);

        $response = $this->getJson($endpoint)->assertNotFound()->json();

        $this->assertSuccessKeyFalse($response);
        $this->assertMessageKey(StoreMessageHelper::STORE_NOT_FOUND, $response);
    }

    private function getStoreEndpoint(int $storeId): string
    {
        return $this->getEndpoint([StoresUrlHelper::STORES, $storeId]);
    }
}

