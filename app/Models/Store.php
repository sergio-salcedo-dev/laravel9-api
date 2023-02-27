<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\StoreFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Store
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @method static StoreFactory factory($count = null, $state = [])
 * @method static Builder|Store newModelQuery()
 * @method static Builder|Store newQuery()
 * @method static Builder|Store query()
 * @method static Builder|Store whereCreatedAt($value)
 * @method static Builder|Store whereName($value)
 * @method static Builder|Store whereUpdatedAt($value)
 * @method static Builder|Store onlyTrashed()
 * @method static Builder|Store whereDeletedAt($value)
 * @method static Builder|Store whereId($value)
 * @method static Builder|Store withTrashed()
 * @method static Builder|Store withoutTrashed()
 * @mixin Eloquent
 */
class Store extends Eloquent
{
    use HasFactory;
    use SoftDeletes;

    public const NAME = 'name';

    protected $fillable = [
        self::NAME,
    ];

    protected $hidden = [
        'deleted_at',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->using(ProductStore::class)
            ->withPivot([ProductStore::STOCK]);
    }
}
