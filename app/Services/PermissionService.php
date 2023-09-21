<?php

namespace App\Services;

use App\Actions\EnsureUserExistsAction;
use App\Actions\EnsureUserIsAdminAction;
use App\Actions\EnsureUserIsSuperAdminAction;
use App\Actions\Permission\AttachPermissionsToRoleAction;
use App\Actions\Permission\CreatePermissionAction;
use App\Actions\Permission\DeletePermissionAction;
use App\Actions\Permission\EnsurePermissionExistsAction;
use App\Actions\Permission\EnsurePermissionsExistAndAreOfSameClassAction;
use App\Actions\Role\EnsureRoleExistsAction;
use App\Actions\Role\EnsureUserCreatedRoleAction;
use App\Actions\Permission\EnsureValidDataExistsAction;
use App\Actions\Permission\UpdatePermissionAction;
use App\Actions\SetAuthorizationClassAction;
use App\DTOs\PermissionDTO;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class PermissionService extends Service
{
    public function createPermission(PermissionDTO $permissionDTO) : Permission
    {
        $permissionDTO = $permissionDTO->withUser(
            $permissionDTO->user ?? User::find($permissionDTO->userId)
        );

        EnsureUserExistsAction::make()->execute($permissionDTO, "user");

        EnsureUserIsSuperAdminAction::make()->execute($permissionDTO);

        $permissionDTO = SetAuthorizationClassAction::make()->execute($permissionDTO);

        EnsureValidDataExistsAction::make()->execute($permissionDTO);

        return CreatePermissionAction::make()->execute($permissionDTO);
    }

    public function updatePermission(PermissionDTO $permissionDTO)
    {
        $permissionDTO = $permissionDTO->withUser(
            $permissionDTO->user ?? User::find($permissionDTO->userId)
        );

        EnsureUserExistsAction::make()->execute($permissionDTO, "user");

        $permissionDTO = $permissionDTO->withPermission(
            $permissionDTO->permission ?? Permission::find($permissionDTO->permissionId)
        );

        EnsurePermissionExistsAction::make()->execute($permissionDTO);

        EnsureUserIsAdminAction::make()->execute($permissionDTO);

        $permissionDTO = SetAuthorizationClassAction::make()->execute($permissionDTO);

        EnsureValidDataExistsAction::make()->execute($permissionDTO, true);

        return UpdatePermissionAction::make()->execute($permissionDTO);
    }
    
    public function deletePermission(PermissionDTO $permissionDTO) : bool
    {
        $permissionDTO = $permissionDTO->withUser(
            $permissionDTO->user ?? User::find($permissionDTO->userId)
        );

        EnsureUserExistsAction::make()->execute($permissionDTO, "user");
        
        $permissionDTO = $permissionDTO->withPermission(
            $permissionDTO->permission ?? Permission::find($permissionDTO->permissionId)
        );

        EnsurePermissionExistsAction::make()->execute($permissionDTO);

        EnsureUserIsSuperAdminAction::make()->execute($permissionDTO);

        return DeletePermissionAction::make()->execute($permissionDTO);
    }
    
    public function attachPermissionsToRole(PermissionDTO $permissionDTO) : Role
    {
        $permissionDTO = $permissionDTO->withUser(
            $permissionDTO->user ?? User::find($permissionDTO->userId)
        );

        EnsureUserExistsAction::make()->execute($permissionDTO, "user");

        $permissionDTO = $permissionDTO->withRole(
            $permissionDTO->role ?? Role::find($permissionDTO->roleId)
        );

        EnsureRoleExistsAction::make()->execute($permissionDTO);

        EnsurePermissionsExistAndAreOfSameClassAction::make()->execute($permissionDTO);

        EnsureUserCreatedRoleAction::make()->execute($permissionDTO);

        return AttachPermissionsToRoleAction::make()->execute($permissionDTO);
    }
}