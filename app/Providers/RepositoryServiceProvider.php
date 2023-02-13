<?php

declare(strict_types=1);

namespace App\Providers;

use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\ProductStoreRepositoryInterface;
use App\Interfaces\ResponderInterface;
use App\Interfaces\StoreRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Repositories\ProductStoreRepository;
use App\Repositories\StoreRepository;
use App\Services\ApiResponderService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(StoreRepositoryInterface::class, StoreRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ProductStoreRepositoryInterface::class, ProductStoreRepository::class);
        $this->app->bind(ResponderInterface::class, ApiResponderService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
