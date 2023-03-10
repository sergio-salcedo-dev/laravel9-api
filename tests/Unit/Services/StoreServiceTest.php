<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Helpers\StoreMessageHelper;
use App\Http\Requests\StoreUpdateOrCreateRequest;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\ResponderInterface;
use App\Interfaces\StoreRepositoryInterface;
use App\Models\Product;
use App\Models\Store;
use App\Services\StoreService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class StoreServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private StoreRepositoryInterface $storeRepository;
    private ResponderInterface $responderService;

    public function setUp(): void
    {
        parent::setUp();

        $this->storeRepository = Mockery::mock(StoreRepositoryInterface::class);
        $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
        $this->responderService = Mockery::mock(ResponderInterface::class);

        $this->service = new StoreService(
            $this->storeRepository,
            $this->productRepository,
            $this->responderService
        );
    }

    public function testGetAllStores_returnJsonResponseWithStatusCode200(): void
    {
        $data = [
            StoreService::KEY_STORES => [
                ['id' => 1, 'name' => 'Store 1'],
                ['id' => 2, 'name' => 'Store 2'],
            ],
        ];

        $this->storeRepository->expects('all')->andReturn(new Collection($data[StoreService::KEY_STORES]));
        $this->responderService->expects('response')->andReturn(new JsonResponse($data));

        $response = $this->service->getAllStores();

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testGetStoresWithProducts_returnsJsonResponseWithStoresWithProductsAndStatusCode200(): void
    {
        $data = [
            StoreService::KEY_STORES => [
                [
                    'id' => 1,
                    'name' => 'Store 1',
                    'products' => [
                        ['id' => 1, 'name' => 'Product 1'],
                        ['id' => 2, 'name' => 'Product 2'],
                    ],
                ],
                [
                    'id' => 2,
                    'name' => 'Store 2',
                    'products' => [
                        ['id' => 3, 'name' => 'Product 1'],
                        ['id' => 4, 'name' => 'Product 2'],
                    ],
                ],
            ],
        ];
        $this->storeRepository->expects('allWithProducts')->andReturn($data);
        $this->responderService->expects('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->getStoresWithProducts();

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testGetStoresWithProductsCount_returnsJsonResponseWithStoresWithProductCountAndStatusCode200(): void
    {
        $data = [
            StoreService::KEY_STORES => [
                ['id' => 1, 'name' => 'Store 1', 'products_count' => 1],
                ['id' => 2, 'name' => 'Store 2', 'products_count' => 1],
            ],
        ];
        $this->storeRepository->expects('allWithProductsCount')->andReturn($data);
        $this->responderService->expects('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->getStoresWithProductsCount();

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testGetStoreWithProducts_withStoreNotFound_returnsJsonResponseWithStatusCode404(): void
    {
        $data = [ResponderInterface::KEY_MESSAGE => StoreMessageHelper::STORE_NOT_FOUND];

        $this->storeRepository->expects('findWithProducts')->andReturnNull();
        $this->responderService->expects('response')->andReturn(new JsonResponse($data, 404));

        $response = $this->service->getStoreWithProducts(1);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 404, $response);
    }

    public function testGetStoreWithProducts_withStore_returnsJsonResponseWithStoreAndProductsAndStatusCode200(): void
    {
        $data = [
            StoreService::KEY_STORES => [
                [
                    'id' => 1,
                    'name' => 'Store 1',
                    'products' => [
                        ['id' => 1, 'name' => 'Product 1'],
                        ['id' => 2, 'name' => 'Product 2'],
                    ],
                ],
                [
                    'id' => 2,
                    'name' => 'Store 2',
                    'products' => [
                        ['id' => 3, 'name' => 'Product 1'],
                        ['id' => 4, 'name' => 'Product 2'],
                    ],
                ],
            ],
        ];
        $store = Mockery::mock(Store::class);

        $this->storeRepository->expects('findWithProducts')->andReturn($store);
        $this->responderService->expects('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->getStoreWithProducts(1);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testGetStore_withStoreNotFound_returnsJsonResponseWithStatusCode404(): void
    {
        $data = [ResponderInterface::KEY_MESSAGE => StoreMessageHelper::STORE_NOT_FOUND];

        $this->storeRepository->expects('find')->andReturnNull();
        $this->responderService->expects('response')->andReturn(new JsonResponse($data, 404));

        $response = $this->service->getStore(1);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 404, $response);
    }

    public function testGetStore_withStore_returnsJsonResponseWithStoreAndStatusCode200(): void
    {
        $data = [StoreService::KEY_STORE => ['id' => 1, 'name' => 'Store 1']];
        $store = Mockery::mock(Store::class);

        $this->storeRepository->expects('find')->andReturn($store);
        $this->responderService->expects('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->getStore(1);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testCreateStore_withFailureCreation_returnsJsonResponseWithExceptionStatusCode500(): void
    {
        $data = [
            'code' => 500,
            'error' => 'exception message',
            'result' => [ResponderInterface::KEY_MESSAGE => StoreMessageHelper::STORE_NOT_CREATED],
        ];
        $request = Mockery::mock(StoreUpdateOrCreateRequest::class);
        $exception = new Exception('exception message');
        $request
            ->shouldReceive('validated')
            ->andReturn([
                'name' => 'My new store',
                'productIds' => [1, 2],
                'products' => [
                    ['id' => 3, 'stock' => 4],
                    ['id' => 4, 'stock' => 4],
                ],
            ]);

        $this->storeRepository->expects('create')->andThrow($exception);
        $this->responderService->expects('sendExceptionError')->andReturn(new JsonResponse($data, 500));

        $response = $this->service->createStore($request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 500, $response);
    }

    public function testCreateStore_withFailureAttachingProducts_returnsJsonResponseWithExceptionAndStatusCode500(
    ): void
    {
        $data = [
            'code' => 500,
            'error' => 'exception message',
            'result' => [
                ResponderInterface::KEY_MESSAGE =>
                    'Warning: The store was created successfully but something went wrong when attaching the products',
            ],
        ];
        $request = Mockery::mock(StoreUpdateOrCreateRequest::class);
        $store = Mockery::mock(Store::class);
        $product = Mockery::mock(Product::class);
        $exception = new Exception('exception message');

        $request
            ->shouldReceive('validated')
            ->andReturn([
                'name' => 'My new store',
                'productIds' => [1, 2],
                'products' => [
                    ['id' => 3, 'stock' => 4],
                    ['id' => 4, 'stock' => 4],
                ],
            ]);

        $this->storeRepository->expects('create')->andReturn($store);
        $this->productRepository
            ->shouldReceive('find')
            ->withAnyArgs()
            ->zeroOrMoreTimes()
            ->andReturn($product);
        $this->storeRepository->expects('attachProduct')->andThrow($exception);
        $this->responderService->expects('sendExceptionError')->andReturn(new JsonResponse($data, 500));

        $response = $this->service->createStore($request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 500, $response);
    }

    public function testCreateStore_withSuccessAndProductsAttached_returnsJsonResponseWithStoreAndProductsAndStatusCode201(
    ): void
    {
        $data = [
            ResponderInterface::KEY_MESSAGE => 'Store created successfully',
            StoreService::KEY_STORE => [
                'id' => 1,
                'name' => 'Store 1',
                'products' => [
                    ['id' => 3, 'stock' => 4],
                    ['id' => 4, 'stock' => 4],
                ],
            ],
        ];

        $request = Mockery::mock(StoreUpdateOrCreateRequest::class);
        $product = Mockery::mock(Product::class);
        $store = Mockery::mock(Store::class)->makePartial();
        $store->id = 1;

        $request
            ->shouldReceive('validated')
            ->andReturn([
                'name' => 'My new store',
                'productIds' => [1, 2],
                'products' => [
                    ['id' => 3, 'stock' => 4],
                    ['id' => 4, 'stock' => 4],
                ],
            ]);

        $this->storeRepository->expects('create')->andReturn($store);
        $this->productRepository
            ->shouldReceive('find')
            ->withAnyArgs()
            ->zeroOrMoreTimes()
            ->andReturn($product);
        $this->storeRepository->shouldReceive('attachProduct')->zeroOrMoreTimes();
        $this->storeRepository->shouldReceive('findWithProducts')->with($store->id)->andReturn($store);
        $this->responderService->expects('response')->andReturn(new JsonResponse($data, 500));
        $store->shouldReceive('makeHidden');

        $response = $this->service->createStore($request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 500, $response);
    }

    public function testUpdateStore_withStoreNotFound_returnsJsonResponseWithStatusCode404(): void
    {
        $data = [ResponderInterface::KEY_MESSAGE => StoreMessageHelper::STORE_NOT_FOUND];
        $request = Mockery::mock(StoreUpdateOrCreateRequest::class);

        $request
            ->shouldReceive('validated')
            ->andReturn([
                'name' => 'My new store',
                'productIds' => [1, 2],
                'products' => [
                    ['id' => 3, 'stock' => 4],
                    ['id' => 4, 'stock' => 4],
                ],
            ]);
        $this->storeRepository->expects('find')->andReturnNull();
        $this->responderService->expects('response')->andReturn(new JsonResponse($data, 404));

        $response = $this->service->updateStore(1, $request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 404, $response);
    }

    public function testUpdateStore_withStoreNotUpdated_returnsJsonResponseWithStatusCode202(): void
    {
        $data = [ResponderInterface::KEY_MESSAGE => StoreMessageHelper::STORE_NOT_UPDATED];
        $request = Mockery::mock(StoreUpdateOrCreateRequest::class);

        $request
            ->shouldReceive('validated')
            ->andReturn([
                'name' => 'My new store',
                'productIds' => [1, 2],
                'products' => [
                    ['id' => 3, 'stock' => 4],
                    ['id' => 4, 'stock' => 4],
                ],
            ]);
        $store = Mockery::mock(Store::class);
        $product = Mockery::mock(Product::class);

        $this->storeRepository->expects('find')->andReturn($store);
        $this->storeRepository->expects('update')->andReturnFalse();
        $this->productRepository->shouldReceive('find')->andReturn($product);
        $this->storeRepository->shouldReceive('syncProduct');
        $this->responderService->expects('response')->andReturn(new JsonResponse($data));
        $store->shouldReceive('refresh');
        $this->storeRepository->shouldReceive('findWithProducts');

        $response = $this->service->updateStore(1, $request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testUpdateStore_withStoreUpdatedAndFailureSyncingProducts_returnsJsonResponseWithStatusCode500(
    ): void
    {
        $data = [
            'code' => 500,
            'error' => 'exception message',
            'result' => [
                ResponderInterface::KEY_MESSAGE => 'Upss... The store was updated successfully but something went wrong when syncing the products',
            ],
        ];
        $request = Mockery::mock(StoreUpdateOrCreateRequest::class);

        $request
            ->shouldReceive('validated')
            ->andReturn([
                'name' => 'Updated store',
                'productIds' => [1, 2],
                'products' => [
                    ['id' => 3, 'stock' => 4],
                    ['id' => 4, 'stock' => 4],
                ],
            ]);
        $store = Mockery::mock(Store::class);
        $product = Mockery::mock(Product::class);
        $exception = new Exception('Exception message');

        $this->storeRepository->shouldReceive('find')->zeroOrMoreTimes()->andReturn($store);
        $this->storeRepository->expects('update')->andReturn(1);

        $store->shouldReceive('refresh');

        $this->productRepository
            ->expects('find')
            ->withAnyArgs()
            ->zeroOrMoreTimes()
            ->andReturn($product);
        $this->storeRepository->shouldReceive('syncProducts')->andThrow($exception);

        $this->responderService->expects('sendExceptionError')->andReturn(new JsonResponse($data, 500));

        $response = $this->service->updateStore(1, $request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 500, $response);
    }

    public function testUpdateStore_withSuccessAndProductsSynced_returnsJsonResponseWithStatusCode200(): void
    {
        $data = [ResponderInterface::KEY_MESSAGE => 'Store updated successfully'];
        $request = Mockery::mock(StoreUpdateOrCreateRequest::class);

        $request
            ->shouldReceive('validated')
            ->andReturn([
                'name' => 'Updated store',
                'productIds' => [1, 2],
                'products' => [
                    ['id' => 3, 'stock' => 4],
                    ['id' => 4, 'stock' => 4],
                ],
            ]);
        $store = Mockery::mock(Store::class);
        $product = Mockery::mock(Product::class);

        $this->storeRepository->shouldReceive('find')->zeroOrMoreTimes()->andReturn($store);
        $this->storeRepository->expects('update')->andReturn(1);

        $store->shouldReceive('refresh');

        $this->productRepository
            ->expects('find')
            ->withAnyArgs()
            ->zeroOrMoreTimes()
            ->andReturn($product);
        $this->storeRepository->expects('syncProduct')->zeroOrMoreTimes();
        $this->storeRepository->expects('findWithProducts')->andReturn($store);

        $this->responderService->expects('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->updateStore(1, $request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testDeleteStore_withStoreNotFound_returnsJsonResponseWithStatusCode404(): void
    {
        $data = [ResponderInterface::KEY_MESSAGE => StoreMessageHelper::STORE_NOT_FOUND];

        $this->storeRepository->expects('find')->andReturnNull();
        $this->responderService->expects('response')->andReturn(new JsonResponse($data, 404));

        $response = $this->service->deleteStore(1);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 404, $response);
    }

    public function testDeleteStore_withStoreNotDeleted_returnsJsonResponseWithStoreAndStatusCode500(): void
    {
        $data = [ResponderInterface::KEY_MESSAGE => 'An error occurred while deleting the store'];

        $product = Mockery::mock(Product::class)->makePartial();
        $products = new Collection([$product]);
        $store = Mockery::mock(Store::class);

        $this->storeRepository->expects('find')->andReturn($store);

        $store->shouldReceive('getAttribute')->with('products')->andReturn($products);
        $store->shouldReceive('load')->with('products')->andReturn([$product]);

        $this->storeRepository->expects('detachProducts');
        $this->storeRepository->expects('delete')->andReturn(0);
        $this->responderService->expects('response')->andReturn(new JsonResponse($data, 500));

        $response = $this->service->deleteStore(1);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 500, $response);
    }

    public function testDeleteStore_withStoreDeleted_returnsJsonResponseWithStoreAndStatusCode200(): void
    {
        $data = [
            ResponderInterface::KEY_MESSAGE => StoreMessageHelper::STORE_DELETED,
            StoreService::KEY_STORE => ['id' => 1, 'name' => 'Store 1'],
        ];
        $product = Mockery::mock(Product::class)->makePartial();
        $products = new Collection([$product]);
        $store = Mockery::mock(Store::class);

        $this->storeRepository->expects('find')->andReturn($store);

        $store->shouldReceive('getAttribute')->with('products')->andReturn($products);
        $store->shouldReceive('load')->with('products')->andReturn([$product]);

        $this->storeRepository->expects('detachProducts');
        $this->storeRepository->expects('delete')->andReturn(1);
        $store->shouldReceive('refresh')->andReturn($store);
        $store->shouldReceive('makeVisible')->andReturn($store);
        $store->shouldReceive('makeHidden');
        $this->responderService->expects('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->deleteStore(1);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }
}
