<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\StoreMessageHelper;
use App\Http\Requests\StoreUpdateOrCreateRequest;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\ResponderInterface;
use App\Interfaces\StoreRepositoryInterface;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class StoreService
{
    public const KEY_STORES = "stores";
    public const KEY_STORE = "store";
    public const KEY_STORE_ID = 'storeId';

    public function __construct(
        private StoreRepositoryInterface $storeRepository,
        private ProductRepositoryInterface $productRepository,
        private ResponderInterface $responderService
    ) {
    }

    public function getAllStores(): Response
    {
        return $this->responderService->response([
            self::KEY_STORES => $this->storeRepository->all(),
        ]);
    }

    public function getStoresWithProducts(): Response
    {
        $stores = $this->storeRepository->allWithProducts();

        return $this->responderService->response([
            self::KEY_STORES => $stores,
        ]);
    }

    public function getStoreWithProducts(int $storeId): Response
    {
        $store = $this->storeRepository->findWithProducts($storeId);

        if (!$store) {
            return $this->returnStoreNotFound();
        }

        return $this->responderService->response([
            self::KEY_STORE => $store,
        ]);
    }

    public function getStoresWithProductsCount(): Response
    {
        return $this->responderService->response([
            self::KEY_STORES => $this->storeRepository->allWithProductsCount(),
        ]);
    }

    public function getStore(int $storeId): Response
    {
        $store = $this->storeRepository->find($storeId);

        if (!$store) {
            return $this->returnStoreNotFound();
        }

        return $this->responderService->response([
            self::KEY_STORE => $store,
        ]);
    }

    public function createStore(StoreUpdateOrCreateRequest $request): Response
    {
        $validatedAttributes = $request->validated();
        $name = Str::of($validatedAttributes[Store::NAME])->trim()->value();
        $productIds = $validatedAttributes[ProductService::KEY_PRODUCT_IDS] ?? [];
        $productsData = $validatedAttributes[ProductService::KEY_PRODUCTS] ?? [];

        try {
            $store = $this->storeRepository->create([Store::NAME => $name]);
        } catch (Throwable $e) {
            $errorInfo = [ResponderInterface::KEY_MESSAGE => StoreMessageHelper::STORE_NOT_CREATED];

            return $this->responderService->sendExceptionError($e, $errorInfo);
        }

        try {
            $productsToAttach = $this->getProductsToAttach($productIds, $productsData);
            $this->attachProductsToStore($store, $productsToAttach);
        } catch (Throwable $e) {
            $errorInfo = [
                ResponderInterface::KEY_MESSAGE =>
                    'Warning: The store was created successfully but something went wrong when attaching the products',
            ];

            return $this->responderService->sendExceptionError($e, $errorInfo);
        }

        return $this->responderService->response([
            ResponderInterface::KEY_MESSAGE => StoreMessageHelper::STORE_CREATED,
            self::KEY_STORE => $this->storeRepository->findWithProducts($store->id)->makeHidden('updated_at'),
        ], Response::HTTP_CREATED);
    }

    public function updateStore(int $storeId, StoreUpdateOrCreateRequest $request): Response
    {
        $validatedAttributes = $request->validated();
        $productIds = $validatedAttributes[ProductService::KEY_PRODUCT_IDS] ?? [];
        $productsData = $validatedAttributes[ProductService::KEY_PRODUCTS] ?? [];

        $store = $this->storeRepository->find($storeId);

        if (!$store) {
            return $this->returnStoreNotFound();
        }

        $name = Str::of($validatedAttributes[Store::NAME] ?? '')->trim()->value() ?: $store->name;

        $isUpdated = $this->storeRepository->update($storeId, [Store::NAME => $name]);

        try {
            $productsToAttach = $this->getProductsToAttach($productIds, $productsData);
            $productsToSync = $this->getProductsToSync($productsToAttach);
            $this->syncProducts($store, $productsToSync);
        } catch (Throwable $e) {
            $errorInfo = [
                ResponderInterface::KEY_MESSAGE =>
                    'Warning: The store was updated successfully but something went wrong when syncing the products',
            ];

            return $this->responderService->sendExceptionError($e, $errorInfo);
        }

        $store->refresh();

        $message = $isUpdated ? StoreMessageHelper::STORE_UPDATED : StoreMessageHelper::STORE_NOT_UPDATED;

        return $this->responderService->response([
            ResponderInterface::KEY_MESSAGE => $message,
            self::KEY_STORE => $this->storeRepository->findWithProducts($storeId),
        ]);
    }

    public function deleteStore(int $storeId): Response
    {
        $store = $this->storeRepository->find($storeId);

        if (!$store) {
            return $this->returnStoreNotFound();
        }

        $this->detachProductsFromStore($store);

        $isDeleted = (bool)$this->storeRepository->delete($storeId);

        if (!$isDeleted) {
            return $this->responderService->response(
                [ResponderInterface::KEY_MESSAGE => StoreMessageHelper::STORE_NOT_DELETED],
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ResponderInterface::VALUE_SUCCESS_FALSE
            );
        }

        $store->refresh();

        return $this->responderService->response([
            ResponderInterface::KEY_MESSAGE => StoreMessageHelper::STORE_DELETED,
            self::KEY_STORE => $store->makeVisible('deleted_at')->makeHidden('updated_at', 'products'),
        ]);
    }

    private function returnStoreNotFound(): Response
    {
        return $this->responderService->response(
            [ResponderInterface::KEY_MESSAGE => StoreMessageHelper::STORE_NOT_FOUND],
            Response::HTTP_NOT_FOUND,
            ResponderInterface::VALUE_SUCCESS_FALSE
        );
    }

    private function attachProductsToStore(Store $store, array $products = []): void
    {
        foreach ($products as $product) {
            $productId = $product['id'];
            unset($product['id']);
            $attributes = array_map('intval', $product);

            $this->storeRepository->attachProduct($store, $productId, $attributes);
        }
    }

    private function detachProductsFromStore(Store $store): void
    {
//        /** @var int[] $productIdsToDetach*/
//        $productIdsToDetach = $store->products()->pluck('products.id')->toArray();
//        $this->storeRepository->detachProductsFromStore($store, $productIdsToDetach);

        $store->load(ProductService::KEY_PRODUCTS);
        $productIdsToDetach = $store->products->pluck('id');
        $productIdsToDetachArray = $productIdsToDetach->toArray();

        $this->storeRepository->detachProducts($store, $productIdsToDetachArray);
    }

    private function mergeProductsDataWithSameProductId(array $productsData): array
    {
        $mergedProductsData = [];

        foreach ($productsData as $productData) {
            $productId = $productData["id"];

            if (isset($mergedProductsData[$productId])) {
                $mergedProductsData[$productId][ProductStore::STOCK] += $productData[ProductStore::STOCK];
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
                ProductStore::STOCK => ResponderInterface::VALUE_SUCCESS_FALSE,
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
                    ProductStore::STOCK => $productData[ProductStore::STOCK] ?? 0,
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
                if (isset($product['id']) && isset($product[ProductStore::STOCK])) {
                    $carry[0][$product['id']] = [ProductStore::STOCK => $product[ProductStore::STOCK]];
                }

                return $carry;
            },
            [[]]
        );
    }

    private function validateProductId(int $productId): int
    {
        $product = $this->productRepository->find($productId);

        if (!$product) {
            return 0;
        }

        return $productId;
    }

    public function syncProducts(Store $store, array $productsToSync): void
    {
        foreach ($productsToSync as $productToSync) {
            $this->storeRepository->syncProduct($store, $productToSync);
        }
    }
}
