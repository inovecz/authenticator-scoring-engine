<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DisposableEmail extends Model
{
    use HasFactory;

    // <editor-fold desc="Region: STATE DEFINITION">
    public $incrementing = false;
    protected $primaryKey = 'domain';
    public $timestamps = false;
    // </editor-fold desc="Region: STATE DEFINITION">

    // <editor-fold desc="Region: GETTERS">
    public function getDomain(): string
    {
        return $this->domain;
    }
    // </editor-fold desc="Region: GETTERS">
}
