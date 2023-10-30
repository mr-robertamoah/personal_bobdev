<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum HeldSessionStateEnum: string
{
    use EnumTrait;
    
    case held = 'HELD';
    case cancelled = 'CANCELLED';
}