<?php

namespace App\Services;

use App\Actions\EnsureUserExistsAction;
use App\Actions\EnsureValidGetAuthorizationsDataAction;
use App\Actions\Permission\EnsureUserIsAuthorizedAction;
use App\Actions\GetModelFromDTOAction;
use App\Actions\Permission\SyncPermissionsAndRoleAction;
use App\Actions\Permission\CreatePermissionAction;
use App\Actions\Permission\DeletePermissionAction;
use App\Actions\Permission\EnsurePermissionExistsAction;
use App\Actions\Permission\EnsurePermissionsExistAndAreOfSameClassAction;
use App\Actions\Role\EnsureRoleExistsAction;
use App\Actions\Role\EnsureUserCreatedRoleAction;
use App\Actions\Permission\EnsureValidDataExistsAction;
use App\Actions\Permission\GetPermissionsAction;
use App\Actions\Permission\UpdatePermissionAction;
use App\Actions\SetAuthorizationClassAction;
use App\DTOs\PermissionDTO;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PermissionService extends Service
{
    public function createPermission(PermissionDTO $permissionDTO) : Permission
    {
        $permissionDTO = $permissionDTO->withUser(
            GetModelFromDTOAction::make()->execute($permissionDTO)
        );

        EnsureUserExistsAction::make()->execute($permissionDTO, "user");

        EnsureUserIsAuthorizedAction::make()->execute($permissionDTO);

        $permissionDTO = SetAuthorizationClassAction::make()->execute($permissionDTO);

        EnsureValidDataExistsAction::make()->execute($permissionDTO);

        return CreatePermissionAction::make()->execute($permissionDTO);
    }

    public function updatePermission(PermissionDTO $permissionDTO)
    {
        $permissionDTO = $permissionDTO->withUser(
            GetModelFromDTOAction::make()->execute($permissionDTO)
        );

        EnsureUserExistsAction::make()->execute($permissionDTO, "user");

        $permissionDTO = $permissionDTO->withPermission(
            GetModelFromDTOAction::make()->execute(
                $permissionDTO, "permission", "permission"
            )
        );

        EnsurePermissionExistsAction::make()->execute($permissionDTO);

        EnsureUserIsAuthorizedAction::make()->execute($permissionDTO, action: "update");

        $permissionDTO = SetAuthorizationClassAction::make()->execute($permissionDTO);

        EnsureValidDataExistsAction::make()->execute($permissionDTO, true);

        return UpdatePermissionAction::make()->execute($permissionDTO);
    }
    
    public function deletePermission(PermissionDTO $permissionDTO) : bool
    {
        $permissionDTO = $permissionDTO->withUser(
            GetModelFromDTOAction::make()->execute($permissionDTO)
        );

        EnsureUserExistsAction::make()->execute($permissionDTO, "user");
        
        $permissionDTO = $permissionDTO->withPermission(
            GetModelFromDTOAction::make()->execute(
                $permissionDTO, "permission", "permission"
            )
        );

        EnsurePermissionExistsAction::make()->execute($permissionDTO);

        EnsureUserIsAuthorizedAction::make()->execute($permissionDTO);

        return DeletePermissionAction::make()->execute($permissionDTO);
    }
    
    public function getPermissions(PermissionDTO $permissionDTO) : LengthAwarePaginator
    {
        $permissionDTO = $permissionDTO->withUser(
            GetModelFromDTOAction::make()->execute($permissionDTO)
        );

        EnsureUserExistsAction::make()->execute($permissionDTO, "user");

        $permissionDTO = SetAuthorizationClassAction::make()->execute($permissionDTO);
        
        EnsureValidGetAuthorizationsDataAction::make()->execute($permissionDTO);

        return GetPermissionsAction::make()->execute($permissionDTO);
    }
    
    public function syncPermissionsAndRole(PermissionDTO $permissionDTO) : Role
    {
        $permissionDTO = $permissionDTO->withUser(
            GetModelFromDTOAction::make()->execute($permissionDTO)
        );

        EnsureUserExistsAction::make()->execute($permissionDTO, "user");

        $permissionDTO = $permissionDTO->withRole(
            GetModelFromDTOAction::make()->execute(
                $permissionDTO, "role", "role"
            )
        );

        EnsureRoleExistsAction::make()->execute($permissionDTO);

        EnsurePermissionsExistAndAreOfSameClassAction::make()->execute($permissionDTO);

        EnsureUserCreatedRoleAction::make()->execute($permissionDTO);

        return SyncPermissionsAndRoleAction::make()->execute($permissionDTO);
    } 
}