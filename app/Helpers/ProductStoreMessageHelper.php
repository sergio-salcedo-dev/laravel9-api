<?php

declare(strict_types=1);

namespace App\Helpers;

class ProductStoreMessageHelper
{
    public const PRODUCT_OUT_STOCK = 'The store does not have any stock of this product.';
    public const STORE_RUN_OUT_STOCK = 'The store run out of this product';
    public const STOCK_LOW = 'The store is running low on stock of this product';
}
