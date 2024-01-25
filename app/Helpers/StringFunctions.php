<?php

declare(strict_types=1);

if (!function_exists('generate_hash')) {
    function generate_hash(bool $orderedUuid = true): string
    {
        return str_replace('-', '', generate_uuid($orderedUuid));
    }
}

if (!function_exists('generate_uuid')) {
    function generate_uuid(bool $orderedUuid = true): string
    {
        return $orderedUuid ? (string) Str::orderedUuid() : (string) Str::uuid();
    }
}

if (!function_exists('get_email_domain')) {
    function get_email_domain(string $email): string
    {
        $explodedEmail = explode('@', $email);
        return array_pop($explodedEmail);
    }
}

if (!function_exists('ip_to_hex')) {
    function ip_to_hex(string $hex): string
    {
        $binaryIP = inet_pton($hex);
        $hexIP = bin2hex($binaryIP);
        $fourF = 'ffff'.$hexIP;
        $paddedHexIP = str_pad($fourF, 32, '0', STR_PAD_LEFT);
        return strtoupper($paddedHexIP);
    }
}

if (!function_exists('hex_to_ip')) {
    function hex_to_ip(string $ip): string
    {
        return inet_ntop(substr(hex2bin($ip), -4));
    }
}
