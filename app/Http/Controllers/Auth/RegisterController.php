<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\Auth\UserRegisteredResource;
use App\Models\User;

class RegisterController extends Controller
{
    public function __invoke(UserRegisterRequest $request): UserRegisteredResource
    {
        $attributes = $this->getAttributes($request);
        $user = User::create($attributes);

        return new UserRegisteredResource($user);
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
