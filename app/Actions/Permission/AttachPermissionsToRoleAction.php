<?php

namespace App\Actions\Permission;
use App\Actions\Action;
use App\DTOs\PermissionDTO;
use App\Models\Role;

class AttachPermissionsToRoleAction extends Action
{
    public function execute(PermissionDTO $permissionDTO): Role
    {
        $permissionDTO->role->permissions()->attach($permissionDTO->permissionIds);

        return $permissionDTO->role->refresh();
    }
}