<?php

namespace App\Actions\Permission;
use App\Actions\Action;
use App\DTOs\PermissionDTO;
use App\Exceptions\PermissionException;

class EnsurePermissionExistsAction extends Action
{
    public function execute(PermissionDTO $permissionDTO)
    {
        if ($permissionDTO->permission) return;

        throw new PermissionException("Sorry! A permission is required to perform this action.", 422);
    }
}