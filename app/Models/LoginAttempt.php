<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\ModelTrait;
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
        'response' => 'array',
    ];
    protected $attributes = [
        'successful' => false,
    ];
    // </editor-fold desc="Region: STATE DEFINITION">

    // <editor-fold desc="Region: BOOT">
    protected static function boot()
    {
        parent::boot();
        static::creating(static function (LoginAttempt $model) {
            $ipAddress = IpAddress::firstOrCreate(['ip' => $model->getIP()]);
            $ipAddress->refresh();
            if ($model->isSuccessful()) {
                $ipAddress->addSuccessfulAttempt();
            } else {
                $ipAddress->addAttempt();
            }
        });
        static::updating(static function (LoginAttempt $model) {
            if ($model->isSuccessful()) {
                $ipAddress = IpAddress::where('ip', $model->getIP())->first();
                $ipAddress->markAttemptSuccessful();
            }
        });
    }
    // </editor-fold desc="Region: BOOT">

    // <editor-fold desc="Region: RELATIONS">
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function ipAddress(): BelongsTo
    {
        return $this->belongsTo(IpAddress::class, 'ip', 'ip');
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

    public function getTimer(): ?float
    {
        return $this->timer;
    }

    public function getMouseMaxSpeed(): ?float
    {
        return $this->mouse_max_speed;
    }

    public function getMouseAvgSpeed(): ?float
    {
        return $this->mouse_avg_speed;
    }

    public function getMouseMaxAccel(): ?float
    {
        return $this->mouse_max_accel;
    }

    public function getMouseAvgAccel(): ?float
    {
        return $this->mouse_avg_accel;
    }

    public function getMouseMovement(): ?int
    {
        return $this->mouse_movement;
    }

    public function getMouseClicks(): ?int
    {
        return $this->mouse_clicks;
    }

    public function getMouseSelections(): ?int
    {
        return $this->mouse_selections;
    }

    public function getMouseScrolls(): ?int
    {
        return $this->mouse_scrolls;
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }
    // </editor-fold desc="Region: GETTERS">

    // <editor-fold desc="Region: ARRAY GETTERS">
    public function getToArrayDefault(): array
    {
        return [
            'login_attempt_id' => $this->getId(),
            'entity' => $this->getEntity(),
            'user_agent' => $this->getUserAgent(),
            'ip' => $this->getIP(),
            'device' => $this->getDevice(),
            'os' => $this->getOS(),
            'browser' => $this->getBrowser(),
            'successful' => $this->isSuccessful(),
            'timer' => $this->getTimer(),
            'mouse_meta' => [
                'max_speed' => $this->getMouseMaxSpeed(),
                'avg_speed' => $this->getMouseAvgSpeed(),
                'max_accel' => $this->getMouseMaxAccel(),
                'avg_accel' => $this->getMouseAvgAccel(),
                'movement' => $this->getMouseMovement(),
                'clicks' => $this->getMouseClicks(),
                'selections' => $this->getMouseSelections(),
                'scrolls' => $this->getMouseScrolls(),
            ],
            'location' => $this->location ? $this->location->getToArray() : null,
            'ip_address' => $this->ipAddress ? $this->ipAddress->getToArray() : null,
            'response' => $this->getResponse(),
            'created_at' => $this->getCreatedAt(),
        ];
    }
    // </editor-fold desc="Region: ARRAY GETTERS">
}
