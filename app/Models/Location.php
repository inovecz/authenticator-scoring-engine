<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{

    // <editor-fold desc="Region: STATE DEFINITION">
    protected $primaryKey = 'ip';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['created_at', 'updated_at'];
    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float',
    ];
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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getCountryCode(): ?string
    {
        return $this->country_code;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }
    // </editor-fold desc="Region: GETTERS">

    // <editor-fold desc="Region: COMPUTED GETTERS">
    // </editor-fold desc="Region: COMPUTED GETTERS">

    // <editor-fold desc="Region: ARRAY GETTERS">
    public function getToArray(): array
    {
        return [
            'ip' => $this->getIP(),
            'attempts' => $this->getAttempts(),
            'successful_attempts' => $this->getSuccessfulAttempts(),
            'success_rate' => $this->getSuccessRate(),
            'city' => $this->getCity(),
            'region' => $this->getRegion(),
            'country' => $this->getCountry(),
            'country_code' => $this->getCountryCode(),
            'longitude' => $this->getLongitude(),
            'latitude' => $this->getLatitude(),
        ];
    }
    // </editor-fold desc="Region: ARRAY GETTERS">

    // <editor-fold desc="Region: FUNCTIONS">
    public function addSuccessAttempt(): float
    {
        $newAttempts = $this->getAttempts() + 1;
        $newSuccessfulAttempts = $this->getSuccessfulAttempts() + 1;
        $newSuccessRate = $newSuccessfulAttempts / $newAttempts;
        $this->update([
            'attempts' => $newAttempts,
            'successful_attempts' => $newSuccessfulAttempts,
            'success_rate' => $newSuccessRate,
        ]);
        return $newSuccessRate;
    }

    public function addFailedAttempt(): float
    {
        $newAttempts = $this->getAttempts() + 1;
        $newSuccessfulAttempts = $this->getSuccessfulAttempts();
        $newSuccessRate = $newSuccessfulAttempts / $newAttempts;
        $this->update([
            'attempts' => $newAttempts,
            'successful_attempts' => $newSuccessfulAttempts,
            'success_rate' => $newSuccessRate,
        ]);
        return $newSuccessRate;
    }
    // </editor-fold desc="Region: FUNCTIONS">

    // <editor-fold desc="Region: SCOPES">
    // </editor-fold desc="Region: SCOPES">

}
