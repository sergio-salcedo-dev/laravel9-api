<?php

declare(strict_types=1);

use App\Helpers\ProductsUrlHelper;
use App\Helpers\StoresUrlHelper;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
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

//Route::middleware('auth:sanctum')->get('/user', fn(Request $request) => $request->user());

Route::post('register', RegisterController::class)->name('user.register');

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
            ['prefix' => StoresUrlHelper::PREFIX_STORES],
            function () {
                Route::get('/products', [StoreController::class, 'storesWithProducts'])->name('stores.products');
                Route::get('/{store}/products', [StoreController::class, 'storeWithProducts'])->name('store.products');
                Route::get('/products-count', [StoreController::class, 'storesWithProductsCount'])
                    ->name('stores.products-count');
            }
        );
        Route::apiResource(StoresUrlHelper::PREFIX_STORES, StoreController::class);
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
            ['prefix' => ProductsUrlHelper::PREFIX_PRODUCTS],
            function () {
                Route::post('/sell', [ProductController::class, 'sell'])->name('products.sell');
            }
        );
        Route::apiResource(ProductsUrlHelper::PREFIX_PRODUCTS, ProductController::class);
    }
);
