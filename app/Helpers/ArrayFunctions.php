<?php

declare(strict_types=1);

if (!function_exists('array_is_numeric')) {
    function array_is_numeric(array $array): bool
    {
        foreach ($array as $value) {
            if (!is_numeric($value)) {
                return false;
            }
        }
        return true;
    }
}
