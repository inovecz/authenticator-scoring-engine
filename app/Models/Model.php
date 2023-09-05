<?php

namespace App\Models;

use Carbon\Carbon;

class Model extends \Illuminate\Database\Eloquent\Model
{
    public function getId(): int|string
    {
        $primaryKey = $this->getPrimaryKeyName();
        return $this->$primaryKey;
    }

    public function getPrimaryKeyName(): string
    {
        return $this->primaryKey;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): Carbon
    {
        return $this->created_at;
    }

    public function getToArray(string $scope = 'default'): array
    {
        $methodName = 'getToArray'.\Str::of($scope)->headline()->replace(' ', '');

        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        return $this->getToArrayDefault();
    }

    public function getToArrayDefault(): array
    {
        return $this->toArray();
    }

}
