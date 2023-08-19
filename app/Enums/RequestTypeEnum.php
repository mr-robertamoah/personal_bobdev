<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum RequestTypeEnum : string
{
    use EnumTrait;
    
    case companyMember = "MEMBER";
    case companyAdmin = "ADMINISTRATOR";
    case ward = "WARD";
    case parent = "PARENT";
    case facilitator = 'FACILITATOR';
    case learner = 'STUDENT';
    case sponsor = 'SPONSOR';

    public  static function learnerAliases() : array
    {
        return ProjectParticipantEnum::LEARNERALIASES;
    }
}