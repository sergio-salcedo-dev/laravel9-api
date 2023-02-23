<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Models\User;

class UserTestHelper
{
    public static function create(array $attributes = [])
    {
        return User::factory()->create($attributes);
    }
}
