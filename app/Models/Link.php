<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LinkFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Link
 *
 * @property int $id
 * @property string $short_link
 * @property string $full_link
 * @property int $user_id
 * @property int $views
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @method static LinkFactory factory($count = null, $state = [])
 * @method static Builder|Link newModelQuery()
 * @method static Builder|Link newQuery()
 * @method static Builder|Link query()
 * @method static Builder|Link whereCreatedAt($value)
 * @method static Builder|Link whereFullLink($value)
 * @method static Builder|Link whereId($value)
 * @method static Builder|Link whereShortLink($value)
 * @method static Builder|Link whereUpdatedAt($value)
 * @method static Builder|Link whereUserId($value)
 * @method static Builder|Link whereViews($value)
 * @mixin Eloquent
 */
class Link extends Model
{
    use HasFactory;

    protected $fillable = [
        'short_link',
        'full_link',
        'user_id',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
