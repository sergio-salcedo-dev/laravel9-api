<?php

declare(strict_types=1);

use App\Helpers\ApiBaseUrlHelper;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
|--------------------------------------------------------------------------
| PHP info
|--------------------------------------------------------------------------
*/

//Route::get('/phpinfo', fn() => phpinfo());

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', fn(Request $request) => $request->user());

/*
|--------------------------------------------------------------------------
| Stores Routes
|--------------------------------------------------------------------------
*/

Route::group(
    [
//        'middleware' => 'auth:sanctum'
    ],
    function () {
        Route::group(
            ['prefix' => ApiBaseUrlHelper::STORES_BASE_URL],
            function () {
                Route::get('/products', [StoreController::class, 'storesWithProducts'])->name('stores.products');
                Route::get('/{id}/products', [StoreController::class, 'storeWithProducts'])->name('store.products');
                Route::get('/products-count', [StoreController::class, 'storesWithProductsCount'])
                    ->name('stores.products-count');
            }
        );
        Route::apiResource(ApiBaseUrlHelper::STORES_BASE_URL, StoreController::class);
    }
);

/*
|--------------------------------------------------------------------------
| Products Routes
|--------------------------------------------------------------------------
*/

Route::group(
    [
//        'middleware' => 'auth:sanctum'
    ],
    function () {
        Route::group(
            ['prefix' => ApiBaseUrlHelper::PRODUCTS_BASE_URL],
            function () {
                Route::post('/sell', [ProductController::class, 'sell'])->name('products.sell');
            }
        );
        Route::apiResource(ApiBaseUrlHelper::PRODUCTS_BASE_URL, ProductController::class);
    }
);
