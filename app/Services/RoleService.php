<?php

namespace App\Services;

use App\Actions\EnsureUserExistsAction;
use App\Actions\Role\CreateRoleAction;
use App\Actions\Permission\EnsureValidDataExistsAction;
use App\Actions\Role\DeleteRoleAction;
use App\Actions\Role\EnsureRoleExistsAction;
use App\Actions\Role\EnsureUserCanCreateRoleAction;
use App\Actions\Role\EnsureUserCanUpateRoleAction;
use App\Actions\Role\UpdateRoleAction;
use App\Actions\SetAuthorizationClassAction;
use App\DTOs\RoleDTO;
use App\Models\Role;
use App\Models\User;

class RoleService extends Service
{
    public function createRole(RoleDTO $roleDTO)
    {
        $roleDTO = $roleDTO->withUser(
            $roleDTO->user ?? User::find($roleDTO->userId)
        );

        EnsureUserExistsAction::make()->execute($roleDTO, "user");

        EnsureUserCanCreateRoleAction::make()->execute($roleDTO);

        $roleDTO = SetAuthorizationClassAction::make()->execute($roleDTO);

        EnsureValidDataExistsAction::make()->execute(
            dto: $roleDTO,
            for: "role"
        );

        return CreateRoleAction::make()->execute($roleDTO);
    }

    public function updateRole(RoleDTO $roleDTO)
    {
        $roleDTO = $roleDTO->withUser(
            $roleDTO->user ?? User::find($roleDTO->userId)
        );

        EnsureUserExistsAction::make()->execute($roleDTO, "user");

        $roleDTO = $roleDTO->withRole(
            $roleDTO->role ?? Role::find($roleDTO->roleId)
        );

        EnsureRoleExistsAction::make()->execute($roleDTO);

        EnsureUserCanCreateRoleAction::make()->execute($roleDTO);

        $roleDTO = SetAuthorizationClassAction::make()->execute($roleDTO);

        EnsureValidDataExistsAction::make()->execute($roleDTO, true);

        return UpdateRoleAction::make()->execute($roleDTO);
    }
    
    public function deleteRole(RoleDTO $roleDTO)
    {
        $roleDTO = $roleDTO->withUser(
            $roleDTO->user ?? User::find($roleDTO->userId)
        );

        EnsureUserExistsAction::make()->execute($roleDTO, "user");

        $roleDTO = $roleDTO->withRole(
            $roleDTO->permission ?? Role::find($roleDTO->permissionId)
        );

        EnsureRoleExistsAction::make()->execute($roleDTO);

        EnsureUserCanUpateRoleAction::make()->execute($roleDTO);

        return DeleteRoleAction::make()->execute($roleDTO);
    }
    
    public function attachPermissionsToRole(RoleDTO $roleDTO)
    {
        // make it a sync so that it is easier to implement on front end
    }
}