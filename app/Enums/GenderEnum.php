<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum GenderEnum: string
{
    use EnumTrait;
    
    case male = 'MALE';
    case female = 'FEMALE';
}