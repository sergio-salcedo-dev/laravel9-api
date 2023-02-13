<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Requests\ProductUpdateOrInsertRequest;
use App\Http\Requests\StoreSellProductRequest;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\ProductStoreRepositoryInterface;
use App\Interfaces\ResponderInterface;
use App\Interfaces\StoreRepositoryInterface;
use App\Models\ProductStore;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ProductService
{
    public function __construct(
        private StoreRepositoryInterface $storeRepository,
        private ProductRepositoryInterface $productRepository,
        private ProductStoreRepositoryInterface $productStoreRepository,
        private StoreService $storeService,
        private ResponderInterface $apiResponderService
    ) {
    }

    public function getAllProducts(): JsonResponse
    {
        return $this->apiResponderService->success([
            'success' => 1,
            'products' => $this->productRepository->getAllProducts(),
        ]);
    }

    public function getProduct(int $productId): JsonResponse
    {
        $product = $this->productRepository->getProductById($productId);

        if (!$product) {
            return $this->returnProductNotFound();
        }

        return $this->apiResponderService->success([
            'success' => 1,
            'product' => $product,
        ]);
    }

    public function createProduct(ProductUpdateOrInsertRequest $request): JsonResponse
    {
        $validatedAttributes = $request->validated();
        $product = $this->productRepository->createProduct([
            'name' => $validatedAttributes['name']
        ]);

        return $this->apiResponderService->created([
            'success' => 1,
            'message' => 'Product created successfully',
            'product' => $product,
        ]);
    }

    public function updateProduct(int $productId, ProductUpdateOrInsertRequest $request): JsonResponse
    {
        $validatedAttributes = $request->validated();
        $product = $this->productRepository->getProductById($productId);

        if (!$product) {
            return $this->returnProductNotFound();
        }

        $isUpdated = $this->productRepository->updateProduct($productId, ['name' => $validatedAttributes['name'],]);

        if (!$isUpdated) {
            return $this->apiResponderService->success([
                'success' => 0,
                'message' => 'An error occurred while updating the product',
            ], HttpResponse::HTTP_ACCEPTED);
        }

        $product->refresh();

        return $this->apiResponderService->success([
            'success' => 1,
            'message' => 'Product updated successfully',
            'product' => $product,
        ]);
    }

    public function deleteProduct(int $productId): JsonResponse
    {
        $product = $this->productRepository->getProductById($productId);

        if (!$product) {
            return $this->returnProductNotFound();
        }

        $isDeleted = (bool)$this->productRepository->deleteProduct($productId);

        if (!$isDeleted) {
            return $this->apiResponderService->error([
                'success' => 0,
                'message' => 'An error occurred while deleting the product',
            ]);
        }

        return $this->apiResponderService->success([
            'success' => 1,
            'message' => 'Product deleted successfully',
            'product' => $product,
        ]);
    }

    public function sellProduct(StoreSellProductRequest $request): JsonResponse
    {
        $validatedAttributes = array_map('intval', $request->validated());
        $storeId = $validatedAttributes['storeId'];
        $productId = $validatedAttributes['productId'];

        $store = $this->storeRepository->getStoreById($storeId);

        if (!$store) {
            return $this->storeService->returnStoreNotFound();
        }

        $product = $this->productRepository->getProductById($productId);

        if (!$product) {
            return $this->returnProductNotFound();
        }

        $productPivot = $this->productStoreRepository->getPivot($store->id, $product->id);
//        $productPivot = $store->products->where('id', $productId)->first()->pivot;

        if (!$productPivot || !$productPivot->hasStock()) {
            $success = 0;
            $message = 'The store does not have any stock of this product.';
        } else {
            $success = 1;
            $productPivot->decrement('stock');
            $message = $this->getProductSoldMessage($productPivot);
        }

        return $this->apiResponderService->success([
            'success' => $success,
            'message' => $message
        ]);
    }

    public function returnProductNotFound($message = "Product not found"): JsonResponse
    {
        return $this->apiResponderService->notFound([
            'success' => 0,
            'message' => $message,
        ]);
    }

    private function getProductSoldMessage(ProductStore $productPivot): string
    {
        $successMessage = 'Product sold successfully.';

        if ($productPivot->stock === 0) {
            $message = "$successMessage The store run out of this product";
        } elseif ($productPivot->isStockRunningLow()) {
            $message = "$successMessage The store is running low on stock of this product, remaining: $productPivot->stock units.";
        } else {
            $message = $successMessage;
        }

        return $message;
    }
}
