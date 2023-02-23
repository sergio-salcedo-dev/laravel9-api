<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Models\User;
use Hash;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    protected $middleware = ['guest'];

    public const TOKEN_NAME = 'api_token';

    public function __invoke(UserLoginRequest $request): User|Response
    {
        $user = User::whereEmail($request->validated('email'))->first();

        if (!$user || !$this->passwordMatchesRecords($request->validated('password'), $user)) {
            return response(['message' => 'Check the given data'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken(self::TOKEN_NAME);

        return response(['token' => $token->plainTextToken]);
    }

    private function passwordMatchesRecords(string $password, User $user): bool
    {
        return Hash::check($password, $user->password);
    }
}
