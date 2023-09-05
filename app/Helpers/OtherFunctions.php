<?php

declare(strict_types=1);

if (!function_exists('determine_range')) {
    function determine_range(int|float|null $value, int $min, int $max, int $step): string
    {
        if (!$value) {
            return "$min-$min";
        }
        for ($i = $min; $i <= $max; $i += $step) {
            $rangeStart = $i;
            $rangeEnd = $i + $step;

            if ($value >= $rangeStart && $value <= $rangeEnd) {
                return "$rangeStart-$rangeEnd";
            }
        }
        return "$max-65535";
    }
}
