<?php

namespace App\Actions\Role;

use App\Actions\Action;
use App\DTOs\RoleDTO;
use App\Models\Role;

class CreateRoleAction extends Action
{
    public function execute(RoleDTO $roleDTO) : Role
    {
        return $roleDTO->user->addedRoles()->create([
            'name' => $roleDTO->name,
            'description' => $roleDTO->description,
            'class' => $roleDTO->class
        ]);
    }
}