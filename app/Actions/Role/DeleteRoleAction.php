<?php

namespace App\Actions\Role;

use App\Actions\Action;
use App\DTOs\RoleDTO;
use App\Models\Authorization;

class DeleteRoleAction extends Action
{
    public function execute(RoleDTO $roleDTO) : bool
    {
        Authorization::query()->whereAuthorization($roleDTO->role)->delete();
        $roleDTO->role->permissions()->detach();
        return (bool) $roleDTO->role->delete();
    }
}