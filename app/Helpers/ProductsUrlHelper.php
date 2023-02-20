<?php

declare(strict_types=1);

namespace App\Helpers;

class ProductsUrlHelper
{
    public const PREFIX_PRODUCTS = "/products";
    public const PRODUCTS = ApiBaseUrlHelper::PREFIX_API . self::PREFIX_PRODUCTS;
}
