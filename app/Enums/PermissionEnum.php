<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum PermissionEnum: string
{
    use EnumTrait;
    
    case UPDATE = "update";
    case DELETE = "delete";
    case CREATESESSION = "create session";
    case UPDATESESSION = "update session";
    case DELETESESSION = "delete session";
    case CREATEHELDSESSION = "create held session";
    case UPDATEHELDSESSION = "update held session";
    case DELETEHELDSESSION = "delete held session";
    case ADDLEARNER = "add learner";
    case REMOVELEARNER = "remove learner";
    case BANLEARNER = "ban learner";
    case ADDFACILITATOR = "add facilitator";
    case REMOVEFACILITATOR = "remove facilitator";
    case BANFACILITATOR = "ban facilitator";
    case ADDSKILLSTOPROJECT = "add skills to project";
    case ADDMEMBER = "add member";
    case REMOVEMEMBER = "remove member";
    case BANMEMBER = "ban member";
    case ADDADMINISTRATOR = "add administrator";
    case REMOVEADMINISTRATOR = "remove administrator";
    case BANADMINISTRATOR = "ban administrator";
    case CREATEPERMISSIONS = "create permissions";
    case CREATEROLES = "create roles";
    case ASSIGNROLES = "assign roles";
    case ASSIGNAUTHORIZATIONS = "assign authorizations";
    case REMOVEAUTHORIZATIONS = "remove authorizations";
    case ASSIGNPERMISSIONS = "assign permissions";
    case MANAGEPROJECTSESSIONS = "manage project sessions";
}