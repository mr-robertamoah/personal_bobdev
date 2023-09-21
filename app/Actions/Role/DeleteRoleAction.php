<?php

namespace App\Actions\Role;

use App\Actions\Action;
use App\DTOs\RoleDTO;

class DeleteRoleAction extends Action
{
    public function execute(RoleDTO $roleDTO) : bool
    {
        // delete authorizations that have this permission as an authorization
        return (bool) $roleDTO->role->delete();
    }
}