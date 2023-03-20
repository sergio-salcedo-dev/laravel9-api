<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Auth\UserBasicResource;
use App\Http\Resources\Links\LinkBasicResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     */
    public function toArray($request): array
    {
        $user = $this;

        return [
            'user' => new UserBasicResource($user),
            'links' => LinkBasicResource::collection($user->links),
            'shortLinks' => $user->links()->select('short_link')->get()->pluck('short_link'),
        ];
    }
}
