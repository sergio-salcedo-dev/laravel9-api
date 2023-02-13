<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProductUpdateOrInsertRequest;
use App\Http\Requests\StoreSellProductRequest;
use App\Interfaces\ResponderInterface;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Throwable;

class ProductController extends Controller
{
    public function __construct(
        private readonly ResponderInterface $apiResponderService,
        private readonly ProductService $productService
    ) {
    }

    /**
     * Display a listing of the Products.
     *
     * @api GET /products
     */
    public function index(): JsonResponse
    {
        try {
            return $this->productService->getAllProducts();
        } catch (Throwable $e) {
            return $this->apiResponderService->sendError500($e);
        }
    }

    /**
     * Display the specified Product.
     *
     * @api GET /products/{id}
     */
    public function show($productId): JsonResponse
    {
        try {
            return $this->productService->getProduct((int)$productId);
        } catch (Throwable $e) {
            return $this->apiResponderService->sendError500($e);
        }
    }

    /**
     * Save a newly created Product in DB.
     *
     * @api POST /products
     * Example body request: JSON { "name" : "Test Product" }
     */
    public function store(ProductUpdateOrInsertRequest $request): JsonResponse
    {
        try {
            return $this->productService->createProduct($request);
        } catch (Throwable $e) {
            return $this->apiResponderService->sendError500($e);
        }
    }

    /**
     * Sell a Product of a specified Product.
     *
     * @api POST /products/sell
     * Example body request: JSON { "storeId":1, "productId" :1 }
     */
    public function sell(StoreSellProductRequest $request): JsonResponse
    {
        try {
            return $this->productService->sellProduct($request);
        } catch (Throwable $e) {
            return $this->apiResponderService->sendError500($e);
        }
    }

    /**
     * Update the specified Product in DB.
     *
     * @api PUT /products/{id}
     */
    public function update($productId, ProductUpdateOrInsertRequest $request): JsonResponse
    {
        try {
            return $this->productService->updateProduct((int)$productId, $request);
        } catch (Throwable $e) {
            return $this->apiResponderService->sendError500($e);
        }
    }

    /**
     * Delete a Product from DB.
     *
     * @api DELETE /products/{id}
     */
    public function destroy($productId): JsonResponse
    {
        try {
            return $this->productService->deleteProduct((int)$productId);
        } catch (Throwable $e) {
            return $this->apiResponderService->sendError500($e);
        }
    }
}
