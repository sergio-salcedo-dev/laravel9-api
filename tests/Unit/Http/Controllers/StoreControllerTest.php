<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\StoreController;
use App\Http\Requests\StoreUpdateOrCreateRequest;
use App\Services\StoreService;
use Illuminate\Http\Response;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private StoreController $controller;
    private StoreService $storeService;
    private Response $expectedResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->expectedResponse = new Response([]);
        $this->storeService = Mockery::mock(StoreService::class);

        $this->controller = new StoreController($this->storeService);
    }

    public function testIndex_returnsExpectedJsonAndStatusCode(): void
    {
        $this->storeService->expects('getAllStores')->andReturn($this->expectedResponse);

        $response = $this->controller->index();

        $this->assertResponseWithStatusCode200($response);
    }

    public function testShow_returnsExpectedJsonAndStatusCode(): void
    {
        $this->storeService->expects('getStore')->withArgs([1])->andReturn($this->expectedResponse);

        $response = $this->controller->show(1);

        $this->assertResponseWithStatusCode200($response);
    }

    public function testStoreWithProducts_returnsExpectedJsonAndStatusCode(): void
    {
        $this->storeService->expects('getStoreWithProducts')->withArgs([1])->andReturn($this->expectedResponse);

        $response = $this->controller->storeWithProducts(1);

        $this->assertResponseWithStatusCode200($response);
    }

    public function testStoresWithProducts_returnsExpectedJsonAndStatusCode(): void
    {
        $this->storeService->expects('getStoresWithProducts')->andReturn($this->expectedResponse);

        $response = $this->controller->storesWithProducts();

        $this->assertResponseWithStatusCode200($response);
    }

    public function testStoresWithProductsCount_returnsExpectedJsonAndStatusCode(): void
    {
        $this->storeService->expects('getStoresWithProductsCount')->andReturn($this->expectedResponse);

        $response = $this->controller->storesWithProductsCount();

        $this->assertResponseWithStatusCode200($response);
    }

    public function testStore_returnsExpectedJsonAndStatusCode(): void
    {
        $request = Mockery::mock(StoreUpdateOrCreateRequest::class);

        $this->storeService->expects('createStore')->with($request)->andReturn($this->expectedResponse);

        $response = $this->controller->store($request);

        $this->assertResponseWithStatusCode200($response);
    }

    public function testUpdate_returnsExpectedJsonAndStatusCode(): void
    {
        $request = Mockery::mock(StoreUpdateOrCreateRequest::class);

        $this->storeService->expects('updateStore')->with(1, $request)->andReturn($this->expectedResponse);

        $response = $this->controller->update(1, $request);

        $this->assertResponseWithStatusCode200($response);
    }

    public function testDestroy_returnsExpectedJsonAndStatusCode(): void
    {
        $this->storeService->expects('deleteStore')->withArgs([1])->andReturn($this->expectedResponse);

        $response = $this->controller->destroy(1);

        $this->assertResponseWithStatusCode200($response);
    }

    private function assertResponseWithStatusCode200(Response $response): void
    {
        $this->assertStatusCode(200, $response);
        $this->assertSame('[]', $response->getContent());
    }
}
