<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreUpdateOrCreateRequest;
use App\Interfaces\ResponderInterface;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Throwable;

class StoreController extends Controller
{
    public function __construct(
        private readonly ResponderInterface $apiResponderService,
        private readonly StoreService $storeService
    ) {
    }

    /**
     * Display a listing of the Stores.
     *
     * @api GET /stores
     */
    public function index(): JsonResponse
    {
        try {
            return $this->storeService->getAllStores();
        } catch (Throwable $e) {
            return $this->apiResponderService->sendError500($e);
        }
    }

    /**
     * Display the specified Store.
     *
     * @api GET /stores/{id}
     */
    public function show($storeId): JsonResponse
    {
        try {
            return $this->storeService->getStore((int)$storeId);
        } catch (Throwable $e) {
            return $this->apiResponderService->sendError500($e);
        }
    }

    /**
     * Display a Store with a listing of its products.
     *
     * @api GET /stores/{id}/products
     */
    public function storeWithProducts($storeId): JsonResponse
    {
        try {
            return $this->storeService->getStoreWithProductsResponse((int)$storeId);
        } catch (Throwable $e) {
            return $this->apiResponderService->sendError500($e);
        }
    }

    /**
     * Display a listing of the Stores with products.
     *
     * @api GET /stores/products
     */
    public function storesWithProducts(): JsonResponse
    {
        try {
            return $this->storeService->getStoresWithProducts();
        } catch (Throwable $e) {
            return $this->apiResponderService->sendError500($e);
        }
    }

    /**
     * Display a listing of the Stores with products count.
     *
     * @api GET /stores/products-count
     */
    public function storesWithProductsCount(): JsonResponse
    {
        try {
            return $this->storeService->getStoresWithProductsCount();
        } catch (Throwable $e) {
            return $this->apiResponderService->sendError500($e);
        }
    }

    /**
     * Save a newly created Store in DB.
     *
     * @api POST /stores
     * Example body request (JSON):
     *       {
     *          "name" : "Test Store",
     *          "productIds" : [1, 2] ,
     *          "products" : [{ "id" : 1, "stock" : 2]
     *      }
     */
    public function store(StoreUpdateOrCreateRequest $request): JsonResponse
    {
        try {
            return $this->storeService->createStore($request);
        } catch (Throwable $e) {
            return $this->apiResponderService->sendError500($e);
        }
    }

    /**
     * Update the specified Store in DB.
     *
     * @api PUT /stores/{id}
     */
    public function update($storeId, StoreUpdateOrCreateRequest $request): JsonResponse
    {
        try {
            return $this->storeService->updateStore((int)$storeId, $request);
        } catch (Throwable $e) {
            return $this->apiResponderService->sendError500($e);
        }
    }

    /**
     * Delete a store from DB.
     *
     * @api DELETE /stores/{id}
     */
    public function destroy($storeId): JsonResponse
    {
        try {
            return $this->storeService->deleteStore((int)$storeId);
        } catch (Throwable $e) {
            return $this->apiResponderService->sendError500($e);
        }
    }
}
