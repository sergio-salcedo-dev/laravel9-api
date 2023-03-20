<?php

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBasicResource extends JsonResource
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
        ];
    }
}
