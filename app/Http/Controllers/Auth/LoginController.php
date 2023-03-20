<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Helpers\UserMessageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Resources\Auth\UserLoggedInResource;
use App\Http\Resources\MessageResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public const TOKEN_NAME = 'api_token';

    public function __invoke(UserLoginRequest $request): JsonResource|Response
    {
        $user = User::whereEmail($request->validated('email'))->first();

        if (!$user || !Hash::check($request->validated('password'), $user->password)) {
            return new MessageResource(UserMessageHelper::INVALID_CREDENTIALS);
        }

        return new UserLoggedInResource($request->user());
    }
}
