<?php

namespace App\Actions\Role;

use App\Actions\Action;
use App\DTOs\RoleDTO;
use App\Exceptions\RoleException;

class EnsureUserCanUpateRoleAction extends Action
{
    public function execute(RoleDTO $roleDTO, string $type = "update")
    {
        if ($roleDTO->user->isAdmin()) return;

        if (
            $roleDTO->user->is($roleDTO->role->user) &&
            $type == "update"
        ) return;

        if (
            $roleDTO->user->is($roleDTO->role->user) &&
            $type == "delete" &&
            $roleDTO->role->isPrivate()
        ) return;

        throw new RoleException("Sorry! You are not authorized to update/delete role with name: {$roleDTO->role->name}.", 422);
    }
}