<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Models\User;

class RegisterController extends Controller
{
    protected $middleware = ['guest'];

    public function __invoke(UserRegisterRequest $request): User
    {
        $attributes = $this->getAttributes($request);

        return User::create($attributes);
    }

    private function bcryptPassword(string $password): string
    {
        return bcrypt($password);
    }

    private function getAttributes(UserRegisterRequest $request)
    {
        $attributes = $request->validated();
        $attributes['password'] = $this->bcryptPassword($request->validated('password'));

        return $attributes;
    }
}
