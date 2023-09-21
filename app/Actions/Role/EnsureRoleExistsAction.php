<?php

namespace App\Actions\Role;
use App\Actions\Action;
use App\Exceptions\RoleException;
use MrRobertAmoah\DTO\BaseDTO;

class EnsureRoleExistsAction extends Action
{
    public function execute(BaseDTO $dto)
    {
        if ($dto->role) {
            return;
        }

        throw new RoleException("Sorry! A valid role is required for this action.", 422);
    }
}