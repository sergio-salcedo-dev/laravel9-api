<?php

declare(strict_types=1);

namespace Tests\Feature\Stores;

use App\Helpers\StoresUrlHelper;
use App\Models\Store;
use App\Services\StoreService;
use Tests\TestCase;

class GetAllStoresTest extends TestCase
{
    public function testGetAllStores_withNoStoresCreated_returnsNoStores(): void
    {
        $response = $this->getJson(StoresUrlHelper::STORES)->assertOk()->json();

        $this->assertSuccessKeyTrue($response);
        $this->assertEmpty($response[StoreService::KEY_STORES]);
    }

    /** @dataProvider providesCounter */
    public function testGetAllStores_withStoresCreated_returnsAllStores(int $storeCount): void
    {
        $stores = Store::factory()->count($storeCount)->create();
        $expectedStores = $stores->toArray();

        $response = $this->getJson(StoresUrlHelper::STORES)->assertOk()->json();
        $actualStores = $response['stores'];

        $this->assertSuccessKeyTrue($response);
        $this->assertCount($storeCount, $actualStores);
        $this->assertEquals($expectedStores[$storeCount - 1], $actualStores[$storeCount - 1]);
    }

    private function providesCounter(): array
    {
        return [
            [1],
            [2],
            [3],
        ];
    }
}
