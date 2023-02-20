<?php

declare(strict_types=1);

namespace App\Providers;

use App\Interfaces\PivotProductStoreRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\ResponderInterface;
use App\Interfaces\StoreRepositoryInterface;
use App\Repositories\PivotProductStoreRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StoreRepository;
use App\Services\ResponderService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /** Register services. */
    public function register(): void
    {
        $this->app->bind(StoreRepositoryInterface::class, StoreRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(PivotProductStoreRepositoryInterface::class, PivotProductStoreRepository::class);
        $this->app->bind(ResponderInterface::class, ResponderService::class);
    }

    /** Bootstrap services. */
    public function boot(): void
    {
        //
    }
}
