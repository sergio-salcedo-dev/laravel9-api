<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Models\User;

class RegisterController extends Controller
{
    public function __invoke(UserRegisterRequest $request): User
    {
        return User::create($request->validated());
    }
}
