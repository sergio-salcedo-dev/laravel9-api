<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\ProductController;
use App\Http\Requests\ProductUpdateOCreateRequest;
use App\Http\Requests\StoreSellProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ProductController $controller;
    private ProductService $productService;

    public function setUp(): void
    {
        parent::setUp();

        $this->productService = Mockery::mock(ProductService::class);

        $this->controller = new ProductController($this->productService);
    }

    public function testIndex_returnsExpectedJsonAndStatusCode(): void
    {
        $this->productService->expects('getAllProducts')->andReturn(new JsonResponse([]));

        $response = $this->controller->index();

        $this->assertJsonResponseWithStatusCode200($response);
    }

    public function testShow_returnsExpectedJsonAndStatusCode(): void
    {
        $this->productService->expects('getProductById')->withArgs([1])->andReturn(new JsonResponse([]));

        $response = $this->controller->show(1);

        $this->assertJsonResponseWithStatusCode200($response);
    }

    public function testSell_returnsExpectedJsonAndStatusCode(): void
    {
        $request = Mockery::mock(StoreSellProductRequest::class);

        $this->productService->expects('sellProduct')->withArgs([$request])->andReturn(new JsonResponse([]));

        $response = $this->controller->sell($request);

        $this->assertJsonResponseWithStatusCode200($response);
    }

    public function testStore_returnsExpectedJsonAndStatusCode(): void
    {
        $request = Mockery::mock(ProductUpdateOCreateRequest::class);

        $this->productService->expects('createProduct')->with($request)->andReturn(new JsonResponse([]));

        $response = $this->controller->store($request);

        $this->assertJsonResponseWithStatusCode200($response);
    }

    public function testUpdate_returnsExpectedJsonAndStatusCode(): void
    {
        $request = Mockery::mock(ProductUpdateOCreateRequest::class);

        $this->productService->expects('updateProduct')->with(1, $request)->andReturn(new JsonResponse([]));

        $response = $this->controller->update(1, $request);

        $this->assertJsonResponseWithStatusCode200($response);
    }

    public function testDestroy_returnsExpectedJsonAndStatusCode(): void
    {
        $this->productService->expects('deleteProduct')->withArgs([1])->andReturn(new JsonResponse([]));

        $response = $this->controller->destroy(1);

        $this->assertJsonResponseWithStatusCode200($response);
    }

    private function assertJsonResponseWithStatusCode200(JsonResponse $response): void
    {
        $this->assertStatusCode(200, $response);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame('[]', $response->getContent());
    }
}
