<?php

declare(strict_types=1);

namespace App\Helpers;

class StoresUrlHelper
{
    public const PREFIX_STORES = "/stores";
    public const STORES = ApiBaseUrlHelper::PREFIX_API . self::PREFIX_STORES;
}
