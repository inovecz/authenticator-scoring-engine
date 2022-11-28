<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    use ModelTrait;

    // <editor-fold desc="Region: STATE DEFINITION">
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = [
        'successful' => 'boolean',
    ];
    // </editor-fold desc="Region: STATE DEFINITION">

    // <editor-fold desc="Region: GETTERS">
    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getCountryCode(): ?string
    {
        return $this->country_code;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function getIP(): ?string
    {
        return $this->ip;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function getDevice(): ?string
    {
        return $this->device;
    }

    public function getOS(): ?string
    {
        return $this->os;
    }

    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }
    // </editor-fold desc="Region: GETTERS">

    // <editor-fold desc="Region: ARRAY GETTERS">
    public function getToArray(): array
    {
        return [
            'entity' => $this->getEntity(),
            'country_code' => $this->getCountryCode(),
            'country' => $this->getCountry(),
            'region' => $this->getRegion(),
            'city' => $this->getCity(),
            'longitude' => $this->getLongitude(),
            'latitude' => $this->getLatitude(),
            'ip' => $this->getIP(),
            'device' => $this->getDevice(),
            'os' => $this->getOS(),
            'browser' => $this->getBrowser(),
            'successful' => $this->isSuccessful(),
        ];
    }
    // </editor-fold desc="Region: ARRAY GETTERS">
}
