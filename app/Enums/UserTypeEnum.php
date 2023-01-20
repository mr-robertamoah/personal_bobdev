<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum UserTypeEnum: string
{
    use EnumTrait;

    case superadmin = 'SUPERADMIN';
    case admin = 'ADMIN';
    case parent = 'PARENT';
    case student = 'STUDENT';
    case donor = 'DONOR';
    case facilitator = 'FACILITATOR';
}