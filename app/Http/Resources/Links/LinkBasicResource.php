<?php

declare(strict_types=1);

namespace App\Http\Resources\Links;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkBasicResource extends JsonResource
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
            'short_link' => $this->short_link,
            'full_link' => $this->full_link,
            'views' => $this->views,
        ];
    }
}
