<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\ProductStore
 *
 * @property int $id
 * @property int $store_id
 * @property int $product_id
 * @property int $stock
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @mixin Eloquent
 */
class ProductStore extends Pivot
{
    public const STOCK_RUNNING_LOW_LIMIT = 5;
    public const STORE_ID = 'store_id';
    public const PRODUCT_ID = 'product_id';
    public const STOCK = 'stock';

    protected $fillable = [
        self::STORE_ID,
        self::PRODUCT_ID,
        self::STOCK,
    ];

    protected $hidden = [
        self::STORE_ID,
        self::PRODUCT_ID,
    ];

    public function hasStock(): bool
    {
        return $this->stock > 0;
    }

    public function isStockRunningLow(): bool
    {
        return $this->stock <= self::STOCK_RUNNING_LOW_LIMIT;
    }

    public function isStockOut(): bool
    {
        return $this->stock === 0;
    }

    public function getStock(): int
    {
        return $this->stock;
    }
}
