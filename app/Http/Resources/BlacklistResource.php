<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BlacklistResource extends JsonResource
{
    public function toArray($request): array|\JsonSerializable|\Illuminate\Contracts\Support\Arrayable
    {
        /** @var \App\Models\Blacklist $this */
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'value' => $this->getValue(),
            'reason' => $this->getReason(),
            'active' => $this->isActive(),
            'created_at' => $this->getCreatedAt(),
        ];
    }
}
