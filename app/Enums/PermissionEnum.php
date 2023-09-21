<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum PermissionEnum: string
{
    use EnumTrait;
    
    case UPDATE = "update";
    case UPDATE = "delete";
    case UPDATE = "create session";
    case UPDATE = "update session";
    case UPDATE = "delete session";
    case UPDATE = "create held session";
    case UPDATE = "update held session";
    case UPDATE = "delete held session";
    case UPDATE = "add learner";
    case UPDATE = "remove learner";
    case UPDATE = "ban learner";
    case UPDATE = "add facilitator";
    case UPDATE = "remove facilitator";
    case UPDATE = "ban facilitator";
    case UPDATE = "add skills to project";
    case UPDATE = "add member";
    case UPDATE = "remove member";
    case UPDATE = "ban member";
    case UPDATE = "add administrator";
    case UPDATE = "remove administrator";
    case UPDATE = "ban administrator";
    case CREATEROLES = "create roles";
    case UPDATE = "assign roles";
    case UPDATE = "assign permissions";
}