<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Requests\StoreUpdateOrCreateRequest;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\ResponderInterface;
use App\Interfaces\StoreRepositoryInterface;
use App\Models\Store;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class StoreService
{
    public function __construct(
        private StoreRepositoryInterface $storeRepository,
        private ProductRepositoryInterface $productRepository,
        private ResponderInterface $apiResponderService
    ) {
    }

    public function getAllStores(): JsonResponse
    {
        return $this->apiResponderService->success([
            'success' => 1,
            'stores' => $this->storeRepository->getAllStores(),
        ]);
    }

    public function getStoresWithProducts(): JsonResponse
    {
        $stores = $this->storeRepository->getStoresWithProducts();

        return $this->apiResponderService->success([
            'success' => 1,
            'stores' => $stores,
        ]);
    }

    public function getStoreById(int $storeId): ?Store
    {
        return $this->storeRepository->getStoreById($storeId);
    }

    public function getStoreWithProducts(int $storeId): Collection|array|null
    {
        return $this->storeRepository->getStoreWithProducts($storeId);
    }

    public function getStoreWithProductsResponse(int $storeId): JsonResponse
    {
        $store = $this->getStoreWithProducts($storeId);

        if (!$store) {
            return $this->returnStoreNotFound();
        }

        return $this->apiResponderService->success([
            'success' => 1,
            'store' => $store,
        ]);
    }

    public function getStoresWithProductsCount(): JsonResponse
    {
        return $this->apiResponderService->success([
            'success' => 1,
            'stores' => $this->storeRepository->getStoresWithProductsCount(),
        ]);
    }

    public function getStore(int $storeId): JsonResponse
    {
        $store = $this->getStoreById($storeId);

        if (!$store) {
            return $this->returnStoreNotFound();
        }

        return $this->apiResponderService->success([
            'success' => 1,
            'store' => $store,
        ]);
    }

    public function createStore(StoreUpdateOrCreateRequest $request): JsonResponse
    {
        $validatedAttributes = $request->validated();
        $name = $validatedAttributes['name'];
        $productIds = $validatedAttributes['productIds'] ?? [];
        $productsData = $validatedAttributes['products'] ?? [];

        try {
            $store = $this->storeRepository->createStore(['name' => $name]);
        } catch (Throwable $e) {
            return $this->apiResponderService->sendError500($e);
        }

        try {
            $productsToAttach = $this->getProductsToAttach($productIds, $productsData);
            $this->attachProductsToStore($store, $productsToAttach);
        } catch (Throwable $e) {
            return $this->apiResponderService->error([
                'success' => 1,
                'message' => 'Upss... The store was created successfully but something went wrong when attaching the products',
                'errors' => $e->getMessage(),
            ]);
        }

        return $this->apiResponderService->created([
            'success' => 1,
            'message' => 'Store created successfully',
            'store' => $this->getStoreWithProducts($store->id),
        ]);
    }

    public function updateStore(int $storeId, StoreUpdateOrCreateRequest $request): JsonResponse
    {
        $validatedAttributes = $request->validated();
        $name = $validatedAttributes['name'];
        $productIds = $validatedAttributes['productIds'] ?? [];
        $productsData = $validatedAttributes['products'] ?? [];

        $store = $this->getStoreById($storeId);

        if (!$store) {
            return $this->returnStoreNotFound();
        }

        $isUpdated = $this->storeRepository->updateStore($storeId, ['name' => $name]);

        if (!$isUpdated) {
            return $this->apiResponderService->success([
                'success' => 0,
                'message' => 'An error occurred while updating the store',
            ], HttpResponse::HTTP_ACCEPTED);
        }

        $store->refresh();

        try {
            $productsToAttach = $this->getProductsToAttach($productIds, $productsData);
            $productsToSync = $this->getProductsToSync($productsToAttach);

            $this->storeRepository->syncProducts($store, $productsToSync);
        } catch (Throwable $e) {
            return $this->apiResponderService->error([
                'success' => 1,
                'message' => 'Upss... The store was updated successfully but something went wrong when syncing the products',
            ]);
        }

        return $this->apiResponderService->success([
            'success' => 1,
            'message' => 'Store updated successfully',
            'store' => $this->getStoreWithProducts($store->id),
        ]);
    }

    public function deleteStore(int $storeId): JsonResponse
    {
        $store = $this->getStoreById($storeId);

        if (!$store) {
            return $this->returnStoreNotFound();
        }

        $this->detachProductsFromStore($store);

        $isDeleted = (bool)$this->storeRepository->deleteStore($storeId);

        if (!$isDeleted) {
            return $this->apiResponderService->error([
                'success' => 0,
                'message' => 'An error occurred while deleting the store',
            ]);
        }

        return $this->apiResponderService->success([
            'success' => 1,
            'message' => 'Store deleted successfully',
            'store' => $store,
        ]);
    }

    public function returnStoreNotFound($message = "Store not found"): JsonResponse
    {
        return $this->apiResponderService->notFound([
            'success' => 0,
            'message' => $message,
        ]);
    }

    private function attachProductsToStore(Store $store, array $products = []): void
    {
        foreach ($products as $product) {
            $productId = $product['id'];
            unset($product['id']);
            $attributes = array_map('intval', $product);

            $this->storeRepository->attachProductToStore($store, $productId, $attributes);
        }
    }

    private function detachProductsFromStore(Store $store): void
    {
        $productIdsToDetach = $store->products()->pluck('products.id')->toArray();

        $this->storeRepository->detachProductsFromStore($store, $productIdsToDetach);
    }

    private function mergeProductsDataWithSameProductId(array $productsData): array
    {
        $mergedProductsData = [];

        foreach ($productsData as $productData) {
            $productId = $productData["id"];

            if (isset($mergedProductsData[$productId])) {
                $mergedProductsData[$productId]["stock"] += $productData["stock"];
            } else {
                $mergedProductsData[$productId] = $productData;
            }
        }

        return array_values($mergedProductsData);
    }

    private function validateProductId(int $productId): int
    {
        $product = $this->productRepository->getProductById($productId);

        if (!$product) {
            return 0;
        }

        return $productId;
    }

    /**
     * @param int[] $productIds
     * @return int[]
     */
    private function getValidatedProductsId(array $productIds): array
    {
        return array_filter(
            array_map(fn(int $productId): int => $this->validateProductId($productId), $productIds)
        );
    }

    /** @param int[] $validatedProductIds */
    private function mapProductIdsToProductData(array $validatedProductIds): array
    {
        return array_map(
            fn(int $validatedProductId): array => [
                "id" => $validatedProductId,
                "stock" => 0,
            ],
            $validatedProductIds
        );
    }

    private function getValidatedProductsData(array $productsData): array
    {
        $validatedProductsData = [];

        foreach ($productsData as $productData) {
            $productId = $productData["id"];
            $validatedProductId = $this->validateProductId($productId);

            if ($validatedProductId) {
                $validatedProductsData[] = [
                    "id" => $validatedProductId,
                    "stock" => $productData['stock'] ?? 0,
                ];
            }
        }

        return $validatedProductsData;
    }

    /**
     * @param int[] $productIds
     * @param array $productsData
     * @return array
     */
    private function getProductsToAttach(array $productIds, array $productsData): array
    {
        $validatedProductIds = $this->getValidatedProductsId($productIds);
        $validatedProductDataFromProductIdsKey = $this->mapProductIdsToProductData($validatedProductIds);

        $validatedProductsData = $this->getValidatedProductsData($productsData);
        $mergedValidatedProductsData = $this->mergeProductsDataWithSameProductId($validatedProductsData);

        $mergedProductsData = [...$validatedProductDataFromProductIdsKey, ...$mergedValidatedProductsData];

        return $this->mergeProductsDataWithSameProductId($mergedProductsData);
    }

    private function getProductsToSync(array $products): array
    {
        return array_reduce(
            $products,
            function ($carry, $product) {
                if (isset($product['id']) && isset($product['stock'])) {
                    $carry[0][$product['id']] = ["stock" => $product['stock']];
                }
                return $carry;
            },
            [[]]
        );
    }
}
