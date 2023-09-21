<?php

namespace App\Actions\Permission;

use App\Actions\Action;
use App\DTOs\PermissionDTO;

class DeletePermissionAction extends Action
{
    public function execute(PermissionDTO $permissionDTO) : bool
    {
        // delete authorizations that have this permission as an authorization
        return (bool) $permissionDTO->permission->delete();
    }
}