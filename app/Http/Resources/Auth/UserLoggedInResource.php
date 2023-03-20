<?php

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLoggedInResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            "isLoggedIn" => true,
//            'accessToken' => $this->getAccessToken($request),
            'isEmailVerified' => (bool)$this->email_verified_at,
            'createdAt' => $this->created_at->diffForHumans(),
        ];
    }

    private function getAccessToken(Request $request): string
    {
        return $request->user()->createToken(LoginController::TOKEN_NAME)->plainTextToken;
    }
}
