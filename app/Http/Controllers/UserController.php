<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\Auth\UserLoggedInResource;
use App\Models\User;

class UserController
{
    public function show(User $user): UserLoggedInResource
    {
        return new UserLoggedInResource($user);
    }
}
