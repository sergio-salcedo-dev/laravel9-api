<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Helpers\ProductMessageHelper;
use App\Helpers\ProductStoreMessageHelper;
use App\Helpers\StoreMessageHelper;
use App\Http\Requests\StoreSellsProductRequest;
use App\Interfaces\PivotProductStoreRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\ResponderInterface;
use App\Interfaces\StoreRepositoryInterface;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use App\Services\ProductService;
use App\Services\StoreService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private StoreRepositoryInterface $storeRepository;
    private ProductRepositoryInterface $productRepository;
    private PivotProductStoreRepositoryInterface $pivotProductStoreRepository;
    private ResponderInterface $responderService;

    public function setUp(): void
    {
        parent::setUp();

        $this->storeRepository = Mockery::mock(StoreRepositoryInterface::class);
        $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
        $this->pivotProductStoreRepository = Mockery::mock(PivotProductStoreRepositoryInterface::class);
        $this->responderService = Mockery::mock(ResponderInterface::class);

        $this->service = new ProductService(
            $this->storeRepository,
            $this->productRepository,
            $this->pivotProductStoreRepository,
            $this->responderService
        );
    }

    public function testGetAllProducts_returnJsonResponseWithStatusCode200(): void
    {
        $data = [
            ProductService::KEY_PRODUCTS => [
                ['id' => 1, 'name' => 'Product 1'],
                ['id' => 2, 'name' => 'Product 2'],
            ],
        ];

        $this->productRepository
            ->expects('all')
            ->andReturn(new Collection($data[ProductService::KEY_PRODUCTS]));
        $this->responderService->expects('response')->andReturn(new JsonResponse($data));

        $response = $this->service->getAllProducts();

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testSellStore_withoutStore_returnsStoreNotFoundWithStatusCode404(): void
    {
        $storeId = 1;
        $productId = 1;
        $request = Mockery::mock(StoreSellsProductRequest::class);
        $data = [ResponderInterface::KEY_MESSAGE => StoreMessageHelper::STORE_NOT_FOUND];

        $request
            ->shouldReceive('validated')
            ->andReturn([
                StoreService::KEY_STORE_ID => $storeId,
                ProductService::KEY_PRODUCT_ID => $productId,
            ]);
        $this->storeRepository->shouldReceive('find')->with($storeId)->andReturnNull();
        $this->responderService->expects('response')->andReturn(new JsonResponse($data, 404));

        $response = $this->service->sellProduct($request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 404, $response);
    }

    public function testSellProduct_withoutProduct_returnsProductNotFoundWithStatusCode404(): void
    {
        $storeId = 1;
        $productId = 1;
        $request = Mockery::mock(StoreSellsProductRequest::class);
        $store = Mockery::mock(Store::class);
        $data = [ResponderInterface::KEY_MESSAGE => ProductMessageHelper::PRODUCT_NOT_FOUND];

        $request
            ->shouldReceive('validated')
            ->andReturn([
                StoreService::KEY_STORE_ID => $storeId,
                ProductService::KEY_PRODUCT_ID => $productId,
            ]);
        $this->storeRepository->shouldReceive('find')->with($storeId)->andReturn($store);
        $this->productRepository->shouldReceive('find')->with($productId)->andReturnNull();
        $this->responderService->shouldReceive('response')->andReturn(new JsonResponse($data, 404));

        $response = $this->service->sellProduct($request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 404, $response);
    }

    public function testSellProduct_withoutPivot_returnsMessageNoStockWithStatusCode200(): void
    {
        $storeId = 1;
        $productId = 1;
        $request = Mockery::mock(StoreSellsProductRequest::class);
        $store = Mockery::mock(Store::class);
        $product = Mockery::mock(Product::class);
        $data = [ResponderInterface::KEY_MESSAGE => ProductStoreMessageHelper::PRODUCT_OUT_STOCK];

        $request
            ->shouldReceive('validated')
            ->andReturn([
                StoreService::KEY_STORE_ID => $storeId,
                ProductService::KEY_PRODUCT_ID => $productId,
            ]);
        $this->storeRepository->shouldReceive('find')->with($storeId)->andReturn($store);
        $this->productRepository->shouldReceive('find')->with($productId)->andReturn($product);
        $this->pivotProductStoreRepository->shouldReceive('getPivotByIds')->with(
            $storeId,
            $productId
        )->andReturnNull();
        $this->responderService->shouldReceive('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->sellProduct($request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testSellProduct_withNoStock_returnsMessageNoStockWithStatusCode200(): void
    {
        $storeId = 1;
        $productId = 1;
        $request = Mockery::mock(StoreSellsProductRequest::class);
        $store = Mockery::mock(Store::class);
        $product = Mockery::mock(Product::class);
        $pivotProductStore = Mockery::mock(ProductStore::class)->shouldAllowMockingProtectedMethods();
        $data = [ResponderInterface::KEY_MESSAGE => ProductStoreMessageHelper::PRODUCT_OUT_STOCK];

        $request
            ->shouldReceive('validated')
            ->andReturn([
                StoreService::KEY_STORE_ID => $storeId,
                ProductService::KEY_PRODUCT_ID => $productId,
            ]);
        $this->storeRepository->shouldReceive('find')->with($storeId)->andReturn($store);
        $this->productRepository->shouldReceive('find')->with($productId)->andReturn($product);
        $this->pivotProductStoreRepository->shouldReceive('getPivotByIds')->with($storeId, $productId)->andReturn(
            $pivotProductStore
        );
        $pivotProductStore->expects('hasStock')->andReturnFalse();
        $this->responderService->shouldReceive('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->sellProduct($request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testSellProduct_withOneProductInStock_decrementsStockAndReturnsProductSoldAndStockRunOutWithStatusCode200(
    ): void
    {
        $storeId = 1;
        $productId = 1;
        $request = Mockery::mock(StoreSellsProductRequest::class);
        $store = Mockery::mock(Store::class);
        $product = Mockery::mock(Product::class);
        $pivotProductStore = Mockery::mock(ProductStore::class)->shouldAllowMockingProtectedMethods();
        $data = [ResponderInterface::KEY_MESSAGE => 'Product sold successfully. The store run out of this product'];

        $request
            ->shouldReceive('validated')
            ->andReturn([
                StoreService::KEY_STORE_ID => $storeId,
                ProductService::KEY_PRODUCT_ID => $productId,
            ]);
        $this->storeRepository->shouldReceive('find')->with($storeId)->andReturn($store);
        $this->productRepository->shouldReceive('find')->with($productId)->andReturn($product);
        $this->pivotProductStoreRepository->shouldReceive('getPivotByIds')->with($storeId, $productId)->andReturn(
            $pivotProductStore
        );
        $pivotProductStore->expects('hasStock')->andReturnTrue();
        $this->pivotProductStoreRepository->expects('decrementStock')->with($pivotProductStore)->andReturn(1);
        $pivotProductStore->expects('isStockOut')->andReturnTrue();
        $this->responderService->shouldReceive('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->sellProduct($request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testSellProduct_withStockRunningLow_decrementsStockAndReturnsProductSoldAndStockRunningLowWithStatusCode200(
    ): void
    {
        $storeId = 1;
        $productId = 1;
        $stock = rand(1, 5);
        $request = Mockery::mock(StoreSellsProductRequest::class);
        $store = Mockery::mock(Store::class);
        $product = Mockery::mock(Product::class);
        $pivotProductStore = Mockery::mock(ProductStore::class)->shouldAllowMockingProtectedMethods();
        $data = [
            ResponderInterface::KEY_MESSAGE =>
                "Product sold successfully. The store is running low on stock of this product, remaining: $stock units",
        ];

        $request
            ->shouldReceive('validated')
            ->andReturn([
                StoreService::KEY_STORE_ID => $storeId,
                ProductService::KEY_PRODUCT_ID => $productId,
            ]);
        $this->storeRepository->shouldReceive('find')->with($storeId)->andReturn($store);
        $this->productRepository->shouldReceive('find')->with($productId)->andReturn($product);
        $this->pivotProductStoreRepository->shouldReceive('getPivotByIds')->with($storeId, $productId)->andReturn(
            $pivotProductStore
        );
        $pivotProductStore->expects('hasStock')->andReturnTrue();
        $this->pivotProductStoreRepository->expects('decrementStock')->with($pivotProductStore)->andReturn(1);
        $pivotProductStore->expects('isStockOut')->andReturnFalse();
        $pivotProductStore->expects('isStockRunningLow')->andReturnTrue();
        $pivotProductStore->expects('getStock')->andReturn($stock);
        $this->responderService->shouldReceive('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->sellProduct($request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testSellProduct_withStock_decrementsStockAndReturnsProductSoldWithStatusCode200(): void
    {
        $storeId = 1;
        $productId = 1;
        $request = Mockery::mock(StoreSellsProductRequest::class);
        $store = Mockery::mock(Store::class);
        $product = Mockery::mock(Product::class);
        $pivotProductStore = Mockery::mock(ProductStore::class)->shouldAllowMockingProtectedMethods();
        $data = [ResponderInterface::KEY_MESSAGE => "Product sold successfully."];

        $request
            ->shouldReceive('validated')
            ->andReturn([
                StoreService::KEY_STORE_ID => $storeId,
                ProductService::KEY_PRODUCT_ID => $productId,
            ]);
        $this->storeRepository->shouldReceive('find')->with($storeId)->andReturn($store);
        $this->productRepository->shouldReceive('find')->with($productId)->andReturn($product);
        $this->pivotProductStoreRepository->shouldReceive('getPivotByIds')->with($storeId, $productId)->andReturn(
            $pivotProductStore
        );
        $pivotProductStore->expects('hasStock')->andReturnTrue();
        $this->pivotProductStoreRepository->expects('decrementStock')->with($pivotProductStore)->andReturn(1);
        $pivotProductStore->expects('isStockOut')->andReturnFalse();
        $pivotProductStore->expects('isStockRunningLow')->andReturnFalse();
        $this->responderService->shouldReceive('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->sellProduct($request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }
}
