<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class IpAddress extends Model
{

    // <editor-fold desc="Region: STATE DEFINITION">
    protected $primaryKey = 'ip';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['created_at', 'updated_at'];
    // </editor-fold desc="Region: STATE DEFINITION">

    // <editor-fold desc="Region: RELATIONS">
    public function loginAttempts(): HasMany
    {
        return $this->hasMany(LoginAttempt::class, 'ip', 'ip');
    }
    // </editor-fold desc="Region: RELATIONS">

    // <editor-fold desc="Region: GETTERS">
    public function getIP(): string
    {
        return $this->ip;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function getSuccessfulAttempts(): int
    {
        return $this->successful_attempts;
    }

    public function getSuccessRate(): float
    {
        return $this->success_rate;
    }

    public function getMlSuccessRate(): float
    {
        return $this->ml_success_rate;
    }
    // </editor-fold desc="Region: GETTERS">

    // <editor-fold desc="Region: ARRAY GETTERS">
    public function getToArrayDefault(): array
    {
        return [
            'ip_address_id' => $this->getId(),
            'ip' => $this->getIP(),
            'attempts' => $this->getAttempts(),
            'successful_attempts' => $this->getSuccessfulAttempts(),
            'success_rate' => $this->getSuccessRate(),
            'ml_success_rate' => $this->getMlSuccessRate(),
        ];
    }

    public function getToArrayRate(): array
    {
        return [
            'attempts' => $this->getAttempts(),
            'successful_attempts' => $this->getSuccessfulAttempts(),
            'success_rate' => $this->getSuccessRate(),
        ];
    }
    // </editor-fold desc="Region: ARRAY GETTERS">

    // <editor-fold desc="Region: FUNCTIONS">
    public function addSuccessAttempt(): float
    {
        $this->update([
            'attempts' => $this->getAttempts() + 1,
            'successful_attempts' => $this->getSuccessfulAttempts() + 1,
        ]);
        return $this->refresh()->getSuccessRate();
    }

    public function markAttemptSuccessful(): float
    {
        $this->update([
            'successful_attempts' => $this->getSuccessfulAttempts() + 1,
        ]);
        return $this->refresh()->getSuccessRate();
    }

    public function addAttempt(): float
    {
        $this->update([
            'attempts' => $this->getAttempts() + 1,
        ]);
        return $this->refresh()->getSuccessRate();
    }
    // </editor-fold desc="Region: FUNCTIONS">
}
