<?php

declare(strict_types=1);

use App\Helpers\ProductsUrlHelper;
use App\Helpers\StoresUrlHelper;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;

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

//Route::group(
//    ['middleware' => 'guest'],
//    function () {
//        Route::post('register', RegisterController::class)->name('register');
//        Route::post('login', LoginController::class)->name('login');
//    }
//);

Route::group(
    ['middleware' => 'auth:sanctum'],
    function () {
//        Route::post('register', RegisterController::class)->name('register');
//        Route::post('login', LoginController::class)->name('login');
//        Route::post('logout', LogoutController::class)->name('logout');

        Route::get('user', fn(Request $request) => $request->user());
        Route::post('user/delete-account', function (Request $request) {
            $request->user()->delete();

            return response()->json(null, \Symfony\Component\HttpFoundation\Response::HTTP_NO_CONTENT);
        })->name('user.delete');
//        Route::get('user', fn(Request $request) => new UserLoggedInResource($request->user()));
        Route::get('/users/{user}', [UserController::class, 'show'])->name('user.show');
    }
);

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

/*
|--------------------------------------------------------------------------
| Links Routes
|--------------------------------------------------------------------------
*/
Route::group(
    [
        'middleware' => 'auth:sanctum',
    ],
    function () {
        Route::group(
            ['prefix' => 'links'],
            function () {
                Route::get('/search/{shortLink}', [LinkController::class, 'search'])->name('links.search');
                Route::delete('/delete-all', [LinkController::class, 'destroyAll'])->name('links.destroy-all');
            }
        );
        Route::apiResource('links', LinkController::class);
    }
);
/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::apiResource('profiles', ProfileController::class)->only('show');
