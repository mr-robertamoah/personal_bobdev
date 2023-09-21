<?php

namespace App\Actions\Permission;
use App\Actions\Action;
use App\DTOs\PermissionDTO;
use App\Models\Permission;

class CreatePermissionAction extends Action
{
    public function execute(PermissionDTO $permissionDTO) : Permission
    {
        return $permissionDTO->user->addedPermissions()->create([
            'name' => $permissionDTO->name,
            'description' => $permissionDTO->description,
            'class' => $permissionDTO->class
        ]);
    }
}