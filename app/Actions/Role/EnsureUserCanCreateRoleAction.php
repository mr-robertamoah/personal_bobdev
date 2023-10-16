<?php

namespace App\Actions\Role;

use App\Actions\Action;
use App\DTOs\RoleDTO;
use App\Enums\PermissionEnum;
use App\Exceptions\RoleException;

class EnsureUserCanCreateRoleAction extends Action
{
    public function execute(RoleDTO $roleDTO)
    {
        if ($roleDTO->user->isAdmin()) return;

        if (
            ($roleDTO->user->ownsCompany() && $this->containsClass($roleDTO, "company")) ||
            ($roleDTO->user->ownsProject() && $this->containsClass($roleDTO, "project")) ||
            $roleDTO->user->isAuthorizedFor(name: PermissionEnum::CREATEROLES->value)
        ) return;

        if ($roleDTO->user->isPermittedTo(PermissionEnum::CREATEROLES->value)) return;

        throw new RoleException("Sorry! You are not authorized to create a role.", 422);
    }

    public function containsClass(RoleDTO $roleDTO, string $classData) : bool
    {
        if (
            is_null($roleDTO->class) ||
            $roleDTO->class == $classData
        ) return true;

        return false;
    }
}