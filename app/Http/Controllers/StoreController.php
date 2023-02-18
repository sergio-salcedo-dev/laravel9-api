<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreUpdateOrCreateRequest;
use App\Services\StoreService;
use Symfony\Component\HttpFoundation\Response;

class StoreController extends Controller
{
    public function __construct(private StoreService $storeService)
    {
    }

    /**
     * Display a listing of the Stores.
     *
     * @api GET /stores
     */
    public function index(): Response
    {
        return $this->storeService->getAllStores();
    }

    /**
     * Display the specified Store.
     *
     * @api GET /stores/{id}
     */
    public function show($storeId): Response
    {
        return $this->storeService->getStore((int)$storeId);
    }

    /**
     * Display a Store with a listing of its products.
     *
     * @api GET /stores/{id}/products
     */
    public function storeWithProducts($storeId): Response
    {
        return $this->storeService->getStoreWithProducts((int)$storeId);
    }

    /**
     * Display a listing of the Stores with products.
     *
     * @api GET /stores/products
     */
    public function storesWithProducts(): Response
    {
        return $this->storeService->getStoresWithProducts();
    }

    /**
     * Display a listing of the Stores with products count.
     *
     * @api GET /stores/products-count
     */
    public function storesWithProductsCount(): Response
    {
        return $this->storeService->getStoresWithProductsCount();
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
    public function store(StoreUpdateOrCreateRequest $request): Response
    {
        return $this->storeService->createStore($request);
    }

    /**
     * Update the specified Store in DB.
     *
     * @api PUT /stores/{id}
     *  Example body request (JSON):
     *       {
     *          "name" : "Test Store",
     *          "productIds" : [1, 2] ,
     *          "products" : [{ "id" : 1, "stock" : 2]
     *      }
     */
    public function update($storeId, StoreUpdateOrCreateRequest $request): Response
    {
        return $this->storeService->updateStore((int)$storeId, $request);
    }

    /**
     * Delete a store from DB.
     *
     * @api DELETE /stores/{id}
     */
    public function destroy($storeId): Response
    {
        return $this->storeService->deleteStore((int)$storeId);
    }
}
