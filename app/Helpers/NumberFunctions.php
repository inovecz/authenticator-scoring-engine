<?php

declare(strict_types=1);

if (!function_exists('weighted_average')) {
    function weighted_average(array $values, array $weights): float
    {
        if (count($values) !== count($weights)) {
            throw new InvalidArgumentException("Arrays of values and weights must have the same length.");
        }

        if (!array_is_numeric($values) || !array_is_numeric($weights)) {
            throw new InvalidArgumentException("Arrays of values and weights must contain only numeric values.");
        }

        $sum = 0;
        $totalWeight = 0;

        for ($i = 0, $iMax = count($values); $i < $iMax; $i++) {
            $sum += $values[$i] * $weights[$i];
            $totalWeight += $weights[$i];
        }

        if ($totalWeight === 0) {
            throw new InvalidArgumentException("Total weight must be greater than 0.");
        }

        return $sum / $totalWeight;
    }
}

if (!function_exists('floor_to')) {
    function floor_to(float|int $value, float|int $multiplicator = 1): float
    {
        if ($multiplicator <= 0) {
            throw new InvalidArgumentException("Multiplicator must be greater than 0.");
        }

        return floor($value / $multiplicator) * $multiplicator;
    }
}

if (!function_exists('ceil_to')) {
    function ceil_to(float|int $value, float|int $multiplicator = 1): float
    {
        if ($multiplicator <= 0) {
            throw new InvalidArgumentException("Multiplicator must be greater than 0.");
        }

        return ceil($value / $multiplicator) * $multiplicator;
    }
}
