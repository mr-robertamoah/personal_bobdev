<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum PaginationEnum: int
{
    use EnumTrait;
    
    case getAuthorizations = 5;
    case getUsers = 10;
}