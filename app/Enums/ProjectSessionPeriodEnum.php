<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum ProjectSessionPeriodEnum: string
{
    use EnumTrait;
    
    case once = 'ONCE';
    case daily = 'DAILY';
    case weekly = 'WEEKLY';
    case monthly = 'MONTHLY';
    case yearly = 'YEARLY';
}