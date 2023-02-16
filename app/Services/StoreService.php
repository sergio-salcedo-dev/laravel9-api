<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Requests\StoreUpdateOrCreateRequest;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\ResponderInterface;
use App\Interfaces\StoreRepositoryInterface;
use App\Models\Store;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class StoreService
{
    public function __construct(
        private StoreRepositoryInterface $storeRepository,
        private ProductRepositoryInterface $productRepository,
        private ResponderInterface $jsonResponderService
    ) {
    }

    public function getAllStores(): Response
    {
        return $this->jsonResponderService->response([
            'success' => 1,
            'stores' => $this->storeRepository->getAllStores(),
        ]);
    }

    public function getStoresWithProducts(): Response
    {
        $stores = $this->storeRepository->getStoresWithProducts();

        return $this->jsonResponderService->response([
            'success' => 1,
            'stores' => $stores,
        ]);
    }

    public function getStoreWithProducts(int $storeId): Response
    {
        $store = $this->storeRepository->getStoreWithProducts($storeId);

        if (!$store) {
            return $this->returnStoreNotFound();
        }

        return $this->jsonResponderService->response([
            'success' => 1,
            'store' => $store,
        ]);
    }

    public function getStoresWithProductsCount(): Response
    {
        return $this->jsonResponderService->response([
            'success' => 1,
            'stores' => $this->storeRepository->getStoresWithProductsCount(),
        ]);
    }

    public function getStore(int $storeId): Response
    {
        $store = $this->storeRepository->getStoreById($storeId);

        if (!$store) {
            return $this->returnStoreNotFound();
        }

        return $this->jsonResponderService->response([
            'success' => 1,
            'store' => $store,
        ]);
    }

    public function createStore(StoreUpdateOrCreateRequest $request): Response
    {
        $validatedAttributes = $request->validated();
        $name = $validatedAttributes['name'];
        $productIds = $validatedAttributes['productIds'] ?? [];
        $productsData = $validatedAttributes['products'] ?? [];

        try {
            $store = $this->storeRepository->createStore(['name' => $name]);
        } catch (Throwable $e) {
            $errorInfo = ['success' => 0, 'message' => 'The store was not created'];

            return $this->jsonResponderService->sendExceptionError($e, $errorInfo);
        }

        try {
            $productsToAttach = $this->getProductsToAttach($productIds, $productsData);
            $this->attachProductsToStore($store, $productsToAttach);
        } catch (Throwable $e) {
            $errorInfo = [
                'success' => 1,
                'message' => 'Warning: The store was created successfully but something went wrong when attaching the products',
            ];

            return $this->jsonResponderService->sendExceptionError($e, $errorInfo);
        }

        return $this->jsonResponderService->response([
            'success' => 1,
            'message' => 'Store created successfully',
            'store' => $this->storeRepository->getStoreWithProducts($store->id),
        ], Response::HTTP_CREATED);
    }

    public function updateStore(int $storeId, StoreUpdateOrCreateRequest $request): Response
    {
        $validatedAttributes = $request->validated();
        $name = $validatedAttributes['name'];
        $productIds = $validatedAttributes['productIds'] ?? [];
        $productsData = $validatedAttributes['products'] ?? [];

        $store = $this->storeRepository->getStoreById($storeId);

        if (!$store) {
            return $this->returnStoreNotFound();
        }

        $isUpdated = $this->storeRepository->updateStore($storeId, ['name' => $name]);

        if (!$isUpdated) {
            return $this->jsonResponderService->response([
                'success' => 0,
                'message' => 'An error occurred while updating the store',
            ], Response::HTTP_ACCEPTED);
        }

        $store->refresh();

        try {
            $productsToAttach = $this->getProductsToAttach($productIds, $productsData);
            $productsToSync = $this->getProductsToSync($productsToAttach);
            $this->storeRepository->syncProducts($store, $productsToSync);
        } catch (Throwable $e) {
            $errorInfo = [
                'success' => 1,
                'message' => 'Upss... The store was updated successfully but something went wrong when syncing the products',
            ];

            return $this->jsonResponderService->sendExceptionError($e, $errorInfo);
        }

        return $this->jsonResponderService->response([
            'success' => 1,
            'message' => 'Store updated successfully',
            'store' => $this->storeRepository->getStoreWithProducts($storeId),
        ]);
    }

    public function deleteStore(int $storeId): Response
    {
        $store = $this->storeRepository->getStoreById($storeId);

        if (!$store) {
            return $this->returnStoreNotFound();
        }

        $this->detachProductsFromStore($store);

        $isDeleted = (bool)$this->storeRepository->deleteStore($storeId);

        if (!$isDeleted) {
            return $this->jsonResponderService->response([
                'success' => 0,
                'message' => 'An error occurred while deleting the store',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->jsonResponderService->response([
            'success' => 1,
            'message' => 'Store deleted successfully',
            'store' => $store,
        ]);
    }

    private function returnStoreNotFound($message = "Store not found"): Response
    {
        return $this->jsonResponderService->response([
            'success' => 0,
            'message' => $message,
        ], Response::HTTP_NOT_FOUND);
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
//        /** @var int[] $productIdsToDetach*/
//        $productIdsToDetach = $store->products()->pluck('products.id')->toArray();
//        $this->storeRepository->detachProductsFromStore($store, $productIdsToDetach);

        $store->load('products');
        $productIdsToDetach = $store->products->pluck('id');
        $productIdsToDetachArray = $productIdsToDetach->toArray();

        $this->storeRepository->detachProductsFromStore($store, $productIdsToDetachArray);
    }

    private function mergeProductsDataWithSameProductId(array $productsData): array
    {
        $mergedProductsData = [];

        foreach ($productsData as $productData) {
            // todo check isset($productData["id"])
            $productId = $productData["id"];

            if (isset($mergedProductsData[$productId])) {
                $mergedProductsData[$productId]["stock"] += $productData["stock"];
            } else {
                $mergedProductsData[$productId] = $productData;
            }
        }

        return array_values($mergedProductsData);
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
            $productId = $productData["id"] ?? 0;

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

    private function getProductsToSync(array $products = []): array
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

    private function validateProductId(int $productId): int
    {
        $product = $this->productRepository->getProductById($productId);

        if (!$product) {
            return 0;
        }

        return $productId;
    }
}
