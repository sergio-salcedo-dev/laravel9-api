<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Requests\ProductUpdateOCreateRequest;
use App\Http\Requests\StoreSellProductRequest;
use App\Interfaces\PivotProductStoreRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\ResponderInterface;
use App\Interfaces\StoreRepositoryInterface;
use App\Models\ProductStore;
use Symfony\Component\HttpFoundation\Response;

class ProductService
{
    public function __construct(
        private StoreRepositoryInterface $storeRepository,
        private ProductRepositoryInterface $productRepository,
        private PivotProductStoreRepositoryInterface $pivotProductStoreRepository,
        private ResponderInterface $jsonResponderService
    ) {
    }

    public function getAllProducts(): Response
    {
        return $this->jsonResponderService->response([
            'success' => 1,
            'products' => $this->productRepository->all(),
        ]);
    }

    public function getProductById(int $productId): Response
    {
        $product = $this->productRepository->find($productId);

        if (!$product) {
            return $this->returnProductNotFound();
        }

        return $this->jsonResponderService->response([
            'success' => 1,
            'product' => $product,
        ]);
    }

    public function createProduct(ProductUpdateOCreateRequest $request): Response
    {
        $validatedAttributes = $request->validated();
        $product = $this->productRepository->createProduct([
            'name' => $validatedAttributes['name'],
        ]);

        return $this->jsonResponderService->response([
            'success' => 1,
            'message' => 'Product created successfully',
            'product' => $product,
        ], Response::HTTP_CREATED);
    }

    public function updateProduct(int $productId, ProductUpdateOCreateRequest $request): Response
    {
        $validatedAttributes = $request->validated();
        $product = $this->productRepository->find($productId);

        if (!$product) {
            return $this->returnProductNotFound();
        }

        $isUpdated = $this->productRepository->updateProduct($productId, ['name' => $validatedAttributes['name'],]);

        if (!$isUpdated) {
            return $this->jsonResponderService->response([
                'success' => 0,
                'message' => 'An error occurred while updating the product',
            ], Response::HTTP_ACCEPTED);
        }

        $product->refresh();

        return $this->jsonResponderService->response([
            'success' => 1,
            'message' => 'Product updated successfully',
            'product' => $product,
        ]);
    }

    public function deleteProduct(int $productId): Response
    {
        $product = $this->productRepository->find($productId);

        if (!$product) {
            return $this->returnProductNotFound();
        }

        $isDeleted = (bool)$this->productRepository->deleteProduct($productId);

        if (!$isDeleted) {
            return $this->jsonResponderService->response([
                'success' => 0,
                'message' => 'An error occurred while deleting the product',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->jsonResponderService->response([
            'success' => 1,
            'message' => 'Product deleted successfully',
            'product' => $product,
        ]);
    }

    public function sellProduct(StoreSellProductRequest $request): Response
    {
        $validatedAttributes = array_map('intval', $request->validated());
        $storeId = $validatedAttributes['storeId'];
        $productId = $validatedAttributes['productId'];

        $store = $this->storeRepository->find($storeId);

        if (!$store) {
            return $this->jsonResponderService->response([
                'success' => 0,
                'message' => "Store not found",
            ], Response::HTTP_NOT_FOUND);
        }

        $product = $this->productRepository->find($productId);

        if (!$product) {
            return $this->returnProductNotFound();
        }

        $pivotProductStore = $this->pivotProductStoreRepository->getPivotByIds($storeId, $productId);

        if (!$pivotProductStore || !$pivotProductStore->hasStock()) {
            $success = 0;
            $message = 'The store does not have any stock of this product.';
        } else {
            $success = 1;
            $this->pivotProductStoreRepository->decrementStock($pivotProductStore);
            $message = $this->getProductSoldMessage($pivotProductStore);
        }

        return $this->jsonResponderService->response([
            'success' => $success,
            'message' => $message,
        ]);
    }

    private function returnProductNotFound($message = "Product not found"): Response
    {
        return $this->jsonResponderService->response([
            'success' => 0,
            'message' => $message,
        ], Response::HTTP_NOT_FOUND);
    }

    private function getProductSoldMessage(ProductStore $pivot): string
    {
        $successMessage = 'Product sold successfully.';

        if ($pivot->isStockOut()) {
            $message = "$successMessage The store run out of this product";
        } elseif ($pivot->isStockRunningLow()) {
            $message = "$successMessage The store is running low on stock of this product, remaining: {$pivot->getStock()} units";
        } else {
            $message = $successMessage;
        }

        return $message;
    }
}
