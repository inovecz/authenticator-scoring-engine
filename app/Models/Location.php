<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{

    // <editor-fold desc="Region: STATE DEFINITION">
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float',
    ];
    // </editor-fold desc="Region: STATE DEFINITION">

    // <editor-fold desc="Region: RELATIONS">
    public function loginAttempts(): HasMany
    {
        return $this->hasMany(LoginAttempt::class, 'location_id', 'id');
    }
    // </editor-fold desc="Region: RELATIONS">

    // <editor-fold desc="Region: GETTERS">
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

    // <editor-fold desc="Region: COMPUTED GETTERS">
    // </editor-fold desc="Region: COMPUTED GETTERS">

    // <editor-fold desc="Region: ARRAY GETTERS">
    public function getToArrayDefault(): array
    {
        return [
            'location_id' => $this->id,
            'city' => $this->getCity(),
            'region' => $this->getRegion(),
            'country' => $this->getCountry(),
            'country_code' => $this->getCountryCode(),
            'longitude' => $this->getLongitude(),
            'latitude' => $this->getLatitude(),
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
        $newAttempts = $this->getAttempts() + 1;
        $newSuccessfulAttempts = $this->getSuccessfulAttempts() + 1;
        $this->update([
            'attempts' => $newAttempts,
            'successful_attempts' => $newSuccessfulAttempts,
        ]);
        return $this->refresh()->getSuccessRate();
    }

    public function addFailedAttempt(): float
    {
        $newAttempts = $this->getAttempts() + 1;
        $this->update([
            'attempts' => $newAttempts,
        ]);
        return $this->refresh()->getSuccessRate();
    }
    // </editor-fold desc="Region: FUNCTIONS">

    // <editor-fold desc="Region: SCOPES">
    // </editor-fold desc="Region: SCOPES">

}
