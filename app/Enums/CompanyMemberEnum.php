<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum CompanyMemberEnum: string
{
    use EnumTrait;
    
    case member = 'MEMBER';
    case manager = 'MANAGER';
}