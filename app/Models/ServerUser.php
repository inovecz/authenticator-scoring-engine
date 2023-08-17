<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use App\Enums\GenderEnum;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class ServerUser extends Model
{
    // <editor-fold desc="Region: STATE DEFINITION">
    protected $connection = 'server';
    protected $table = 'users';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $hidden = ['password'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'gender' => GenderEnum::class,
    ];
    // </editor-fold desc="Region: STATE DEFINITION">

    // <editor-fold desc="Region: GETTERS">
    public function getName(): string
    {
        return $this->name;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function getGender(): GenderEnum
    {
        return $this->gender;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getLastAttemptAt(): ?Carbon
    {
        return $this->last_attempt_at;
    }

    public function getLoginCount(): int
    {
        return $this->login_count;
    }

    public function getAverageScore(): float
    {
        return $this->average_score;
    }

    // </editor-fold desc="Region: GETTERS">

    // <editor-fold desc="Region: COMPUTED GETTERS">
    public function getFullName(bool $reverse = false, bool $ascii = false): string
    {
        $fullname = collect([$this->getName(), $this->getSurname()]);
        if ($ascii) {
            $fullname->each(fn(string $string) => Str::ascii($string));
        }
        return $reverse ? $fullname->reverse()->implode(' ') : $fullname->implode(' ');
    }
    // </editor-fold desc="Region: COMPUTED GETTERS">
}
