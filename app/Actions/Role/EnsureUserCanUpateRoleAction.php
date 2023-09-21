<?php

namespace App\Actions\Role;

use App\Actions\Action;
use App\DTOs\RoleDTO;
use App\Exceptions\RoleException;

class EnsureUserCanUpateRoleAction extends Action
{
    public function execute(RoleDTO $roleDTO)
    {
        if ($roleDTO->user->isAdmin()) return;

        if ($roleDTO->user->is($roleDTO->role->user)) return;

        throw new RoleException("Sorry! You are not authorized to update/delete role with name: {$roleDTO->role->name}.", 422);
    }
}