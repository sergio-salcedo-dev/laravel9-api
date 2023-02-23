<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Helpers\UserMessageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Resources\Auth\UserLoggedInResource;
use Auth;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public const TOKEN_NAME = 'api_token';

    public function __invoke(UserLoginRequest $request): JsonResource|Response
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response(['message' => UserMessageHelper::INVALID_CREDENTIALS], Response::HTTP_UNAUTHORIZED);
        }

        return new UserLoggedInResource($request->user());
    }
}
