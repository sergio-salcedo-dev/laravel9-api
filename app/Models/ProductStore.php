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
    public const STOCK_RUNNING_LOW = 5;

    protected $fillable = [
        'store_id',
        'product_id',
        'stock',
    ];

    protected $hidden = [
        'store_id',
        'product_id',
    ];

    public function hasStock(): bool
    {
        return $this->stock > 0;
    }

    public function isStockRunningLow(): bool
    {
        return $this->stock <= self::STOCK_RUNNING_LOW;
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
