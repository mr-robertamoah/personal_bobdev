<?php

namespace App\Actions\Permission;
use App\Actions\Action;
use App\DTOs\PermissionDTO;
use App\Exceptions\PermissionException;
use App\Models\Permission;

class EnsurePermissionsExistAndAreOfSameClassAction extends Action
{
    public function execute(PermissionDTO $permissionDTO)
    {
        if (!count($permissionDTO->permissionIds)) {
            throw new PermissionException("Sorry! You are required to provide id of permissions.", 422);
        }

        $validPermissions = Permission::whereIn('id', $permissionDTO->permissionIds)
            ->get();
        $validPermissionsArray = $validPermissions->toArray();
        
        if (!count($validPermissionsArray)) {
            throw new PermissionException("Sorry! all permissions provided do not exist.", 422);
        }
        
        if (
            count($permissionDTO->permissionIds) !=
            count($validPermissionsArray)
        ) {
            $invalidPermissionsStr = implode(", ", 
                array_diff(
                    $permissionDTO->permissionIds, 
                    array_map(fn($val) => $val["id"], $validPermissionsArray)
                ));
    
            throw new PermissionException(
                "Sorry! Permissions with [{$invalidPermissionsStr}] ids are not valid.", 422
            );
        };

        if (is_null($permissionDTO->role->class)) return;

        $validPermissionsArray = $validPermissions->filter(function ($value) use ($permissionDTO) {
            if (
                is_null($value->class) ||
                $value->class == $permissionDTO->role->class
            ) return true;
        })->toArray();

        if (
            count($permissionDTO->permissionIds) !=
            count($validPermissionsArray)
        ) {
            $invalidPermissionsStr = implode(", ",
                array_diff(
                    $permissionDTO->permissionIds,
                    array_map(fn($val) => $val["id"], $validPermissionsArray)
                ));
    
            throw new PermissionException(
                "Sorry! Permissions with [{$invalidPermissionsStr}] ids do not have valid class to be attached to the role.", 422
            );
        };
    }
}