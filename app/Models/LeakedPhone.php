<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeakedPhone extends Model
{
    use HasFactory;

    // <editor-fold desc="Region: STATE DEFINITION">
    public $incrementing = false;
    protected $primaryKey = 'phone';
    public $timestamps = false;
    // </editor-fold desc="Region: STATE DEFINITION">

    // <editor-fold desc="Region: GETTERS">
    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getLeaks(): int
    {
        return $this->leaks;
    }
    // </editor-fold desc="Region: GETTERS">
}
