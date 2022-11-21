<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeakedEmail extends Model
{
    use HasFactory;

    // <editor-fold desc="Region: STATE DEFINITION">
    public $incrementing = false;
    protected $primaryKey = 'email';
    public $timestamps = false;
    // </editor-fold desc="Region: STATE DEFINITION">

    // <editor-fold desc="Region: GETTERS">
    public function getEmail(): string
    {
        return $this->email;
    }

    public function getLeaks(): int
    {
        return $this->leaks;
    }
    // </editor-fold desc="Region: GETTERS">
}
