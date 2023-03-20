<?php

declare(strict_types=1);

namespace App\Http\Resources\Links;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ErrorMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     */
    public function toArray($request): array
    {
        self::withoutWrapping();

        return [
            'message' => $this->resource['message'],
            'errors' => [
                $this->resource['key'] => [$this->resource['message']],
            ],
        ];
    }
}
