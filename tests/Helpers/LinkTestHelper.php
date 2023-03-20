<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Http\Resources\Links\LinkResource;
use App\Models\Link;
use App\Models\User;
use Carbon\Carbon;

class LinkTestHelper
{
    public static function create(array $attributes = []): Link
    {
        $user = User::factory()->create();

        if (empty($attributes)) {
            return Link::factory()->create([
                'short_link' => 'test.com',
                'full_link' => 'https://test.com',
                'user_id' => $user->id,
            ]);
        }

        $attributes['user_id'] = $user->id;

        return Link::factory()->create($attributes);
    }

    public static function make(array $attributes = [], ?User $user = null): Link
    {
        $a_user = $user ?? User::factory()->create();

        if (empty($attributes)) {
            return Link::factory()->make([
                'id' => 1,
                'short_link' => 'test.com',
                'full_link' => 'https://www.test.com',
                'user_id' => $a_user->id,
                'views' => 0,
                'created_at' => Carbon::now(),
            ]);
        }

        $attributes['user_id'] = $a_user->id;

        return Link::factory()->make($attributes);
    }

    public static function getLinkResource(Link $link): array
    {
        return (new LinkResource($link))->toArray($link);
    }
}
