<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum ProjectParticipantEnum : string
{
    use EnumTrait;
    
    case facilitator = 'FACILITATOR';
    case learner = 'STUDENT';
    case sponsor = 'SPONSOR';
}