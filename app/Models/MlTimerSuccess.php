<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasCompositePrimaryKey;

class MlTimerSuccess extends Model
{
    use HasCompositePrimaryKey;

    // <editor-fold desc="Region: STATE DEFINITION">
    protected $primaryKey = ['from', 'to'];
    public $incrementing = false;
    protected $guarded = [];
    public $timestamps = false;
    // </editor-fold desc="Region: STATE DEFINITION">

    // <editor-fold desc="Region: GETTERS">
    public function getMlSuccessRate(): float
    {
        return $this->ml_success_rate;
    }
    // </editor-fold desc="Region: GETTERS">

    // <editor-fold desc="Region: FUNCTIONS">
    public static function getMlSuccessRateByValue(float|int|null $value): float
    {
        $range = determine_range($value, 0, 180, 2);
        [$from, $to] = explode('-', $range);

        return self::query()
            ->where('from', $from)
            ->where('to', $to)
            ->first()?->getMlSuccessRate() ?? 0.5;
    }
    // </editor-fold desc="Region: FUNCTIONS">
}
