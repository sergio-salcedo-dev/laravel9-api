<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProductUpdateOCreateRequest;
use App\Http\Requests\StoreSellProductRequest;
use App\Services\ProductService;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService)
    {
    }

    /**
     * Display a listing of the Products.
     *
     * @api GET /products
     */
    public function index(): Response
    {
        return $this->productService->getAllProducts();
    }

    /**
     * Display the specified Product.
     *
     * @api GET /products/{id}
     */
    public function show($productId): Response
    {
        return $this->productService->getProductById((int)$productId);
    }

    /**
     * Save a newly created Product in DB.
     *
     * @api POST /products
     * Example body request: JSON { "name" : "Test Product" }
     */
    public function store(ProductUpdateOCreateRequest $request): Response
    {
        return $this->productService->createProduct($request);
    }

    /**
     * Sell a Product of a specified Product.
     *
     * @api POST /products/sell
     * Example body request: JSON { "storeId":1, "productId" :1 }
     */
    public function sell(StoreSellProductRequest $request): Response
    {
        return $this->productService->sellProduct($request);
    }

    /**
     * Update the specified Product in DB.
     *
     * @api PUT /products/{id}
     */
    public function update($productId, ProductUpdateOCreateRequest $request): Response
    {
        return $this->productService->updateProduct((int)$productId, $request);
    }

    /**
     * Delete a Product from DB.
     *
     * @api DELETE /products/{id}
     */
    public function destroy($productId): Response
    {
        return $this->productService->deleteProduct((int)$productId);
    }
}
