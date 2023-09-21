<?php

namespace App\Actions\Role;
use App\Actions\Action;
use App\Exceptions\RoleException;
use MrRobertAmoah\DTO\BaseDTO;

class EnsureUserCreatedRoleAction extends Action
{
    public function execute(BaseDTO $dto)
    {
        if ($dto->user->isAdmin() || $dto->user->is($dto->role->user)) {
            return;
        }

        throw new RoleException("Sorry! You are not authorized to perform this action on {$dto->role->name} role.", 422);
    }
}