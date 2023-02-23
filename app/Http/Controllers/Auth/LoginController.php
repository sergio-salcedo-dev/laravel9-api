<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Helpers\UserMessageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use Auth;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public const TOKEN_NAME = 'api_token';
    public const ACCESS_TOKEN_KEY = 'access_token';

    public function __invoke(UserLoginRequest $request): Response
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response(['message' => UserMessageHelper::INVALID_CREDENTIALS], Response::HTTP_UNAUTHORIZED);
        }

        $token = $request->user()->createToken(self::TOKEN_NAME);

        return response([self::ACCESS_TOKEN_KEY => $token->plainTextToken]);
    }
}
