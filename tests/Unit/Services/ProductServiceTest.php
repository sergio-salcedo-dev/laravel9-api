<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\ProductStoreRepositoryInterface;
use App\Interfaces\ResponderInterface;
use App\Interfaces\StoreRepositoryInterface;
use App\Models\Product;
use App\Services\ProductService;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    private ProductRepositoryInterface $productRepository;
    private StoreRepositoryInterface $storeRepository;
    private ProductStoreRepositoryInterface $productStoreRepository;
    private ResponderInterface $apiResponseService;

    public function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->storeRepository = $this->createMock(StoreRepositoryInterface::class);
        $this->productStoreRepository = $this->createMock(ProductStoreRepositoryInterface::class);
        $this->storeService = $this->createMock(StoreService::class);
        $this->apiResponseService = $this->createMock(ResponderInterface::class);

        $this->productService = new ProductService(
            $this->storeRepository,
            $this->productRepository,
            $this->productStoreRepository,
            $this->storeService,
            $this->apiResponseService
        );
    }

    public function testGetAllProducts(): void
    {
        $product = $this->createMock(Product::class);

        $this->productRepository
            ->expects($this->once())
            ->method('getAllProducts')
            ->willReturn([$product]);

        $this->apiResponseService
            ->expects($this->once())
            ->method('success')
            ->willReturn(
                new JsonResponse([
                    'success' => 1,
                    'products' => [$product],
                ])
            );

        $response = $this->productService->getAllProducts();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }
}
