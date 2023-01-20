<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum RequestStateEnum: string
{
    use EnumTrait;

    case pending = 'PENDING';
    case accepted = 'ACCEPTED';
    case declined = 'DECLINED';

    public static function possibleResponse()
    {
        return [
            static::accepted->value,
            static::declined->value,
        ];
    }
}