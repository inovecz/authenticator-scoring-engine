<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoginAttemptResource extends JsonResource
{
    public function toArray($request): array|\JsonSerializable|\Illuminate\Contracts\Support\Arrayable
    {
        /** @var \App\Models\LoginAttempt $this */
        return $this->getToArray();
    }
}
