<?php
declare(strict_types=1);
namespace App\Enums;

use App\Enums\Traits\EnumTrait;

enum GenderEnum: string
{
    use EnumTrait;

    case MALE = 'MALE';
    case FEMALE = 'FEMALE';
    case OTHER = 'OTHER';
}
