<?php

namespace App\Actions\Permission;
use App\Actions\Action;
use App\DTOs\PermissionDTO;
use App\Models\Role;

class SyncPermissionsAndRoleAction extends Action
{
    public function execute(PermissionDTO $permissionDTO): Role
    {
        $relatedIds = $permissionDTO->role->permissions()->allRelatedIds()->toArray();

        $permissionDTO->role->permissions()->detach(
            array_intersect($permissionDTO->permissionIds, $relatedIds)
        );
        $permissionDTO->role->permissions()->attach(
            array_diff($permissionDTO->permissionIds, $relatedIds)
        );

        return $permissionDTO->role->refresh();
    }
}