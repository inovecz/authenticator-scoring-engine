<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginAttempt extends Model
{
    use ModelTrait;

    // <editor-fold desc="Region: STATE DEFINITION">
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = [
        'js_run' => 'boolean',
        'is_google' => 'boolean',
        'new_visit' => 'boolean',
        'successful' => 'boolean',
    ];
    protected $attributes = [
        'successful' => false,
    ];
    // </editor-fold desc="Region: STATE DEFINITION">

    // <editor-fold desc="Region: BOOT">
    protected static function boot()
    {
        parent::boot();
        static::created(static function (LoginAttempt $model) {
            $location = Location::where('ip', $model->getIP())->first();
            $isSuccessful = $model->isSuccessful();
            $location?->update([
                'attempts' => $location->getAttempts() + 1,
                'successful_attempts' => $isSuccessful ? $location->getSuccessfulAttempts() + 1 : $location->getSuccessfulAttempts(),
                'success_rate' => ($isSuccessful ? $location->getSuccessfulAttempts() + 1 : $location->getSuccessfulAttempts()) / ($location->getAttempts() + 1),
            ]);
        });
        static::updated(static function (LoginAttempt $model) {
            if ($model->isSuccessful()) {
                $location = Location::where('ip', $model->getIP())->first();
                $location?->update([
                    'successful_attempts' => $location->getSuccessfulAttempts() + 1,
                    'success_rate' => ($location->getSuccessfulAttempts() + 1) / $location->getAttempts(),
                ]);
            }
        });
    }
    // </editor-fold desc="Region: BOOT">

    // <editor-fold desc="Region: RELATIONS">
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'ip', 'ip');
    }
    // </editor-fold desc="Region: RELATIONS">

    // <editor-fold desc="Region: GETTERS">
    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getIP(): ?string
    {
        return $this->ip;
    }

    public function isJsRun(): bool
    {
        return $this->js_run;
    }

    public function isGoogle(): bool
    {
        return $this->is_google;
    }

    public function getStatusCode(): ?int
    {
        return $this->status_code;
    }

    public function isNewVisit(): bool
    {
        return $this->new_visit;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function getUserAgent(): ?string
    {
        return $this->user_agent;
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
