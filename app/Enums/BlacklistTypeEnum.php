<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\EnumTrait;

enum BlacklistTypeEnum: string
{
    use EnumTrait;

    case IP = 'IP';
    case DOMAIN = 'DOMAIN';
    case EMAIL = 'EMAIL';
    case OS = 'OS';
}
