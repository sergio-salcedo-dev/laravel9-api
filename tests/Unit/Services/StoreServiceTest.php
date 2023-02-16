<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

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
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class StoreServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private StoreRepositoryInterface $storeRepository;
    private ResponderInterface $jsonResponderService;

    public function setUp(): void
    {
        parent::setUp();

        $this->storeRepository = Mockery::mock(StoreRepositoryInterface::class);
        $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
        $this->jsonResponderService = Mockery::mock(ResponderInterface::class);

        $this->service = new StoreService(
            $this->storeRepository,
            $this->productRepository,
            $this->jsonResponderService
        );
    }

    public function testGetAllStores_returnJsonResponseWithStatusCode200(): void
    {
        $data = [
            'success' => 1,
            'stores' => [
                ['id' => 1, 'name' => 'Store 1'],
                ['id' => 2, 'name' => 'Store 2'],
            ],
        ];

        $this->storeRepository->expects('getAllStores')->andReturn(new Collection($data['stores']));
        $this->jsonResponderService->expects('response')->andReturn(new JsonResponse($data));

        $response = $this->service->getAllStores();

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testGetStoresWithProducts_returnsJsonResponseWithStoresWithProductsAndStatusCode200(): void
    {
        $data = [
            'success' => 1,
            'stores' => [
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
        $this->storeRepository->expects('getStoresWithProducts')->andReturn($data);
        $this->jsonResponderService->expects('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->getStoresWithProducts();

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testGetStoresWithProductsCount_returnsJsonResponseWithStoresWithProductCountAndStatusCode200(): void
    {
        $data = [
            'success' => 1,
            'stores' => [
                ['id' => 1, 'name' => 'Store 1', 'products_count' => 1],
                ['id' => 2, 'name' => 'Store 2', 'products_count' => 1],
            ],
        ];
        $this->storeRepository->expects('getStoresWithProductsCount')->andReturn($data);
        $this->jsonResponderService->expects('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->getStoresWithProductsCount();

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testGetStoreWithProducts_withStoreNotFound_returnsJsonResponseWithStatusCode404(): void
    {
        $data = [
            'success' => 0,
            'message' => 'Store not found',
        ];

        $this->storeRepository->expects('getStoreWithProducts')->andReturnNull();
        $this->jsonResponderService->expects('response')->andReturn(new JsonResponse($data, 404));

        $response = $this->service->getStoreWithProducts(1);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 404, $response);
    }

    public function testGetStoreWithProducts_withStore_returnsJsonResponseWithStoreAndProductsAndStatusCode200(): void
    {
        $data = [
            'success' => 1,
            'stores' => [
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

        $this->storeRepository->expects('getStoreWithProducts')->andReturn($store);
        $this->jsonResponderService->expects('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->getStoreWithProducts(1);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testGetStore_withStoreNotFound_returnsJsonResponseWithStatusCode404(): void
    {
        $data = [
            'success' => 0,
            'message' => 'Store not found',
        ];

        $this->storeRepository->expects('getStoreById')->andReturnNull();
        $this->jsonResponderService->expects('response')->andReturn(new JsonResponse($data, 404));

        $response = $this->service->getStore(1);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 404, $response);
    }

    public function testGetStore_withStore_returnsJsonResponseWithStoreAndStatusCode200(): void
    {
        $data = [
            'success' => 1,
            'store' => ['id' => 1, 'name' => 'Store 1'],
        ];
        $store = Mockery::mock(Store::class);

        $this->storeRepository->expects('getStoreById')->andReturn($store);
        $this->jsonResponderService->expects('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->getStore(1);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testCreateStore_withFailureCreation_returnsJsonResponseWithExceptionStatusCode500(): void
    {
        $data = [
            'code' => 500,
            'error' => 'exception message',
            'result' => [
                'success' => 0,
                'message' => 'The store was not created',
            ],
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

        $this->storeRepository->expects('createStore')->andThrow($exception);
        $this->jsonResponderService->expects('sendExceptionError')->andReturn(new JsonResponse($data, 500));

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
                'success' => 1,
                'message' => 'Warning: The store was created successfully but something went wrong when attaching the products',
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

        $this->storeRepository->expects('createStore')->andReturn($store);
        $this->productRepository
            ->shouldReceive('getProductById')
            ->withAnyArgs()
            ->zeroOrMoreTimes()
            ->andReturn($product);
        $this->storeRepository->expects('attachProductToStore')->andThrow($exception);
        $this->jsonResponderService->expects('sendExceptionError')->andReturn(new JsonResponse($data, 500));

        $response = $this->service->createStore($request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 500, $response);
    }

    public function testCreateStore_withSuccessAndProductsAttached_returnsJsonResponseWithStoreAndProductsAndStatusCode201(
    ): void
    {
        $data = [
            'success' => 1,
            'message' => 'Store created successfully',
            'store' => [
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

        $this->storeRepository->expects('createStore')->andReturn($store);
        $this->productRepository
            ->shouldReceive('getProductById')
            ->withAnyArgs()
            ->zeroOrMoreTimes()
            ->andReturn($product);
        $this->storeRepository->shouldReceive('attachProductToStore')->zeroOrMoreTimes();
        $this->storeRepository->shouldReceive('getStoreWithProducts')->with($store->id)->andReturn($store);
        $this->jsonResponderService->expects('response')->andReturn(new JsonResponse($data, 500));

        $response = $this->service->createStore($request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 500, $response);
    }


    public function testUpdateStore_withStoreNotFound_returnsJsonResponseWithStatusCode404(): void
    {
        $data = [
            'success' => 0,
            'message' => "Store not found",
        ];
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
        $this->storeRepository->expects('getStoreById')->andReturnNull();
        $this->jsonResponderService->expects('response')->andReturn(new JsonResponse($data, 404));

        $response = $this->service->updateStore(1, $request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 404, $response);
    }

    public function testUpdateStore_withStoreNotUpdated_returnsJsonResponseWithStatusCode202(): void
    {
        $data = [
            'success' => 0,
            'message' => 'An error occurred while updating the store',
        ];
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

        $this->storeRepository->expects('getStoreById')->andReturn($store);
        $this->storeRepository->expects('updateStore')->andReturnFalse();

        $this->jsonResponderService->expects('response')->andReturn(new JsonResponse($data, Response::HTTP_ACCEPTED));

        $response = $this->service->updateStore(1, $request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 202, $response);
    }

    public function testUpdateStore_withStoreUpdatedAndFailureSyncingProducts_returnsJsonResponseWithStatusCode500(
    ): void
    {
        $data = [
            'code' => 500,
            'error' => 'exception message',
            'result' => [
                'success' => 0,
                'message' => 'Upss... The store was updated successfully but something went wrong when syncing the products',
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

        $this->storeRepository->shouldReceive('getStoreById')->zeroOrMoreTimes()->andReturn($store);
        $this->storeRepository->expects('updateStore')->andReturn(1);

        $store->shouldReceive('refresh');

        $this->productRepository
            ->expects('getProductById')
            ->withAnyArgs()
            ->zeroOrMoreTimes()
            ->andReturn($product);
        $this->storeRepository->shouldReceive('syncProducts')->andThrow($exception);

        $this->jsonResponderService->expects('sendExceptionError')->andReturn(new JsonResponse($data, 500));

        $response = $this->service->updateStore(1, $request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 500, $response);
    }

    public function testUpdateStore_withSuccessAndProductsSynced_returnsJsonResponseWithStatusCode200(): void
    {
        $data = [
            'success' => 1,
            'message' => 'Store updated successfully',
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

        $this->storeRepository->shouldReceive('getStoreById')->zeroOrMoreTimes()->andReturn($store);
        $this->storeRepository->expects('updateStore')->andReturn(1);

        $store->shouldReceive('refresh');

        $this->productRepository
            ->expects('getProductById')
            ->withAnyArgs()
            ->zeroOrMoreTimes()
            ->andReturn($product);
        $this->storeRepository->expects('syncProducts');
        $this->storeRepository->expects('getStoreWithProducts')->andReturn($store);

        $this->jsonResponderService->expects('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->updateStore(1, $request);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

    public function testDeleteStore_withStoreNotFound_returnsJsonResponseWithStatusCode404(): void
    {
        $data = [
            'success' => 0,
            'message' => "Store not found",
        ];

        $this->storeRepository->expects('getStoreById')->andReturnNull();
        $this->jsonResponderService->expects('response')->andReturn(new JsonResponse($data, 404));

        $response = $this->service->deleteStore(1);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 404, $response);
    }

    public function testDeleteStore_withStoreNotDeleted_returnsJsonResponseWithStoreAndStatusCode500(): void
    {
        $data = [
            'success' => 0,
            'message' => 'An error occurred while deleting the store',
        ];

        $product = Mockery::mock(Product::class)->makePartial();
        $products = new Collection([$product]);
        $store = Mockery::mock(Store::class);

        $this->storeRepository->expects('getStoreById')->andReturn($store);

        $store->shouldReceive('getAttribute')->with('products')->andReturn($products);
        $store->shouldReceive('load')->with('products')->andReturn([$product]);

        $this->storeRepository->expects('detachProductsFromStore');
        $this->storeRepository->expects('deleteStore')->andReturn(0);
        $this->jsonResponderService->expects('response')->andReturn(new JsonResponse($data, 500));

        $response = $this->service->deleteStore(1);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 500, $response);
    }

    public function testDeleteStore_withStoreDeleted_returnsJsonResponseWithStoreAndStatusCode200(): void
    {
        $data = [
            'success' => 1,
            'message' => 'Store deleted successfully',
            'store' => ['id' => 1, 'name' => 'Store 1'],
        ];
        $product = Mockery::mock(Product::class)->makePartial();
        $products = new Collection([$product]);
        $store = Mockery::mock(Store::class);

        $this->storeRepository->expects('getStoreById')->andReturn($store);

        $store->shouldReceive('getAttribute')->with('products')->andReturn($products);
        $store->shouldReceive('load')->with('products')->andReturn([$product]);

        $this->storeRepository->expects('detachProductsFromStore');
        $this->storeRepository->expects('deleteStore')->andReturn(1);
        $this->jsonResponderService->expects('response')->andReturn(new JsonResponse($data, 200));

        $response = $this->service->deleteStore(1);

        $this->assertJsonResponseAndStatusCode(json_encode($data), 200, $response);
    }

}
