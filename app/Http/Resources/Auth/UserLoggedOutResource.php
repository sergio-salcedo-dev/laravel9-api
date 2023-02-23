<?php

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLoggedOutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'message' => 'Logged out successfully',
            'isLoggedIn' => false,
        ];
    }

    private function getAccessToken(Request $request): string
    {
        return $request->user()->createToken(LoginController::TOKEN_NAME)->plainTextToken;
    }
}
