<?php

declare(strict_types=1);

if (!function_exists('get_email_domain')) {
    function get_email_domain(string $email): string
    {
        $explodedEmail = explode('@', $email);
        return array_pop($explodedEmail);
    }
}
