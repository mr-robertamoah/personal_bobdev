<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum ProjectSessionTypeEnum: string
{
    use EnumTrait;
    
    case online = 'ONLINE';
    case offline = 'OFFLINE';
}