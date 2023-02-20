<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\ProductController;
use App\Http\Requests\ProductUpdateOCreateRequest;
use App\Http\Requests\StoreSellProductRequest;
use App\Services\ProductService;
use Illuminate\Http\Response;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ProductController $controller;
    private ProductService $productService;
    private Response $expectedResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->expectedResponse = new Response([]);
        $this->productService = Mockery::mock(ProductService::class);

        $this->controller = new ProductController($this->productService);
    }

    public function testIndex_returnsExpectedJsonAndStatusCode(): void
    {
        $this->productService->expects('getAllProducts')->andReturn($this->expectedResponse);

        $response = $this->controller->index();

        $this->assertResponseWithStatusCode200($response);
    }

    public function testShow_returnsExpectedJsonAndStatusCode(): void
    {
        $this->productService->expects('getProductById')->withArgs([1])->andReturn($this->expectedResponse);

        $response = $this->controller->show(1);

        $this->assertResponseWithStatusCode200($response);
    }

    public function testSell_returnsExpectedJsonAndStatusCode(): void
    {
        $request = Mockery::mock(StoreSellProductRequest::class);

        $this->productService->expects('sellProduct')->withArgs([$request])->andReturn($this->expectedResponse);

        $response = $this->controller->sell($request);

        $this->assertResponseWithStatusCode200($response);
    }

    public function testStore_returnsExpectedJsonAndStatusCode(): void
    {
        $request = Mockery::mock(ProductUpdateOCreateRequest::class);

        $this->productService->expects('createProduct')->with($request)->andReturn($this->expectedResponse);

        $response = $this->controller->store($request);

        $this->assertResponseWithStatusCode200($response);
    }

    public function testUpdate_returnsExpectedJsonAndStatusCode(): void
    {
        $request = Mockery::mock(ProductUpdateOCreateRequest::class);

        $this->productService->expects('updateProduct')->with(1, $request)->andReturn($this->expectedResponse);

        $response = $this->controller->update(1, $request);

        $this->assertResponseWithStatusCode200($response);
    }

    public function testDestroy_returnsExpectedJsonAndStatusCode(): void
    {
        $this->productService->expects('deleteProduct')->withArgs([1])->andReturn($this->expectedResponse);

        $response = $this->controller->destroy(1);

        $this->assertResponseWithStatusCode200($response);
    }

    private function assertResponseWithStatusCode200(Response $response): void
    {
        $this->assertStatusCode(200, $response);
        $this->assertSame('[]', $response->getContent());
    }
}
