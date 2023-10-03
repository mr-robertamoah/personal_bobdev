<?php

namespace App\Services;

use App\Actions\EnsureUserExistsAction;
use App\Actions\EnsureValidGetAuthorizationsDataAction;
use App\Actions\GetModelFromDTOAction;
use App\Actions\Role\CreateRoleAction;
use App\Actions\Permission\EnsureValidDataExistsAction;
use App\Actions\Role\DeleteRoleAction;
use App\Actions\Role\EnsureRoleExistsAction;
use App\Actions\Role\EnsureUserCanCreateRoleAction;
use App\Actions\Role\EnsureUserCanUpateRoleAction;
use App\Actions\Role\GetRolesAction;
use App\Actions\Role\UpdateRoleAction;
use App\Actions\SetAuthorizationClassAction;
use App\DTOs\PermissionDTO;
use App\DTOs\RoleDTO;
use App\Models\Role;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleService extends Service
{
    public function createRole(RoleDTO $roleDTO)
    {
        $roleDTO = $roleDTO->withUser(
            GetModelFromDTOAction::make()->execute($roleDTO)
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
            GetModelFromDTOAction::make()->execute($roleDTO)
        );

        EnsureUserExistsAction::make()->execute($roleDTO, "user");

        $roleDTO = $roleDTO->withRole(
            GetModelFromDTOAction::make()->execute($roleDTO, "role")
        );

        EnsureRoleExistsAction::make()->execute($roleDTO);

        EnsureUserCanUpateRoleAction::make()->execute($roleDTO);

        $roleDTO = SetAuthorizationClassAction::make()->execute($roleDTO);

        EnsureValidDataExistsAction::make()->execute($roleDTO, true, "role");

        return UpdateRoleAction::make()->execute($roleDTO);
    }
    
    public function deleteRole(RoleDTO $roleDTO)
    {
        $roleDTO = $roleDTO->withUser(
            GetModelFromDTOAction::make()->execute($roleDTO)
        );

        EnsureUserExistsAction::make()->execute($roleDTO, "user");

        $roleDTO = $roleDTO->withRole(
            GetModelFromDTOAction::make()->execute($roleDTO, "role")
        );

        EnsureRoleExistsAction::make()->execute($roleDTO);

        EnsureUserCanUpateRoleAction::make()->execute($roleDTO);

        return DeleteRoleAction::make()->execute($roleDTO);
    }
    
    public function getRoles(RoleDTO $roleDTO) : LengthAwarePaginator
    {
        $roleDTO = $roleDTO->withUser(
            GetModelFromDTOAction::make()->execute($roleDTO)
        );

        EnsureUserExistsAction::make()->execute($roleDTO, "user");

        $roleDTO = SetAuthorizationClassAction::make()->execute($roleDTO);
        
        EnsureValidGetAuthorizationsDataAction::make()->execute($roleDTO, type: "role");

        return GetRolesAction::make()->execute($roleDTO);
    }
    
    public function syncPermissionsAndRole(RoleDTO $roleDTO)
    {
        (new PermissionService)->syncPermissionsAndRole(
            PermissionDTO::new()->fromArray([
                "user" => $roleDTO->user,
                "userId" => $roleDTO->userId,
                "role" => $roleDTO->role,
                "roleId" => $roleDTO->roleId,
                "permissionIds" => $roleDTO->permissionIds

            ])
        );
    }
}