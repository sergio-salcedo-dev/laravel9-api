<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\ProductMessageHelper;
use App\Helpers\ProductStoreMessageHelper;
use App\Helpers\StoreMessageHelper;
use App\Http\Requests\ProductUpdateOCreateRequest;
use App\Http\Requests\StoreSellProductRequest;
use App\Interfaces\PivotProductStoreRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\ResponderInterface;
use App\Interfaces\StoreRepositoryInterface;
use App\Models\Product;
use App\Models\ProductStore;
use Symfony\Component\HttpFoundation\Response;

class ProductService
{
    public const KEY_PRODUCTS = "products";
    public const KEY_PRODUCT = "product";
    public const KEY_PRODUCT_IDS = "productIds";
    public const KEY_PRODUCT_ID = 'productId';

    public function __construct(
        private StoreRepositoryInterface $storeRepository,
        private ProductRepositoryInterface $productRepository,
        private PivotProductStoreRepositoryInterface $pivotProductStoreRepository,
        private ResponderInterface $responderService
    ) {
    }

    public function getAllProducts(): Response
    {
        return $this->responderService->response([
            self::KEY_PRODUCTS => $this->productRepository->all(),
        ]);
    }

    public function getProductById(int $productId): Response
    {
        $product = $this->productRepository->find($productId);

        if (!$product) {
            return $this->returnProductNotFound();
        }

        return $this->responderService->response([
            self::KEY_PRODUCT => $product,
        ]);
    }

    public function createProduct(ProductUpdateOCreateRequest $request): Response
    {
        $validatedAttributes = $request->validated();
        $product = $this->productRepository->create([
            Product::NAME => $validatedAttributes[Product::NAME],
        ]);

        return $this->responderService->response([
            ResponderInterface::KEY_MESSAGE => ProductMessageHelper::PRODUCT_CREATED,
            self::KEY_PRODUCT => $product,
        ], Response::HTTP_CREATED);
    }

    public function updateProduct(int $productId, ProductUpdateOCreateRequest $request): Response
    {
        $validatedAttributes = $request->validated();
        $product = $this->productRepository->find($productId);

        if (!$product) {
            return $this->returnProductNotFound();
        }

        $isUpdated = $this->productRepository->update(
            $productId,
            [Product::NAME => $validatedAttributes[Product::NAME]]
        );

        if (!$isUpdated) {
            return $this->responderService->response(
                [ResponderInterface::KEY_MESSAGE => ProductMessageHelper::PRODUCT_NOT_UPDATED],
                Response::HTTP_ACCEPTED,
                ResponderInterface::VALUE_SUCCESS_FALSE
            );
        }

        $product->refresh();

        return $this->responderService->response([
            ResponderInterface::KEY_MESSAGE => ProductMessageHelper::PRODUCT_UPDATED,
            self::KEY_PRODUCT => $product,
        ]);
    }

    public function deleteProduct(int $productId): Response
    {
        $product = $this->productRepository->find($productId);

        if (!$product) {
            return $this->returnProductNotFound();
        }

        $isDeleted = (bool)$this->productRepository->delete($productId);

        if (!$isDeleted) {
            return $this->responderService->response(
                [ResponderInterface::KEY_MESSAGE => ProductMessageHelper::PRODUCT_NOT_DELETED],
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ResponderInterface::VALUE_SUCCESS_FALSE
            );
        }

        return $this->responderService->response([
            ResponderInterface::KEY_MESSAGE => ProductMessageHelper::PRODUCT_DELETED,
            self::KEY_PRODUCT => $product,
        ]);
    }

    public function sellProduct(StoreSellProductRequest $request): Response
    {
        $validatedAttributes = array_map('intval', $request->validated());
        $storeId = $validatedAttributes[StoreService::KEY_STORE_ID];
        $productId = $validatedAttributes[self::KEY_PRODUCT_ID];

        $store = $this->storeRepository->find($storeId);

        if (!$store) {
            return $this->responderService->response(
                [ResponderInterface::KEY_MESSAGE => StoreMessageHelper::STORE_NOT_FOUND],
                Response::HTTP_NOT_FOUND,
                ResponderInterface::VALUE_SUCCESS_FALSE
            );
        }

        $product = $this->productRepository->find($productId);

        if (!$product) {
            return $this->returnProductNotFound();
        }

        $pivotProductStore = $this->pivotProductStoreRepository->getPivotByIds($storeId, $productId);

        if (!$pivotProductStore || !$pivotProductStore->hasStock()) {
            $message = ProductStoreMessageHelper::PRODUCT_OUT_STOCK;
        } else {
            $this->pivotProductStoreRepository->decrementStock($pivotProductStore);
            $message = $this->getProductSoldMessage($pivotProductStore);
        }

        return $this->responderService->response([
            ResponderInterface::KEY_MESSAGE => $message,
        ]);
    }

    private function returnProductNotFound(): Response
    {
        return $this->responderService->response(
            [ResponderInterface::KEY_MESSAGE => ProductMessageHelper::PRODUCT_NOT_FOUND],
            Response::HTTP_NOT_FOUND,
            ResponderInterface::VALUE_SUCCESS_FALSE
        );
    }

    private function getProductSoldMessage(ProductStore $pivot): string
    {
        $successMessage = ProductMessageHelper::PRODUCT_SOLD;

        if ($pivot->isStockOut()) {
            $message = $successMessage . ' ' . ProductStoreMessageHelper::STORE_RUN_OUT_STOCK;
        } elseif ($pivot->isStockRunningLow()) {
            $message = $successMessage . ' ' . ProductStoreMessageHelper::STOCK_LOW . ", remaining: {$pivot->getStock()} units";
        } else {
            $message = $successMessage;
        }

        return $message;
    }
}
