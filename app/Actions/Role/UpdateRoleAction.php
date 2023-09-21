<?php

namespace App\Actions\Role;

use App\Actions\Action;
use App\DTOs\RoleDTO;
use App\Models\Role;

class UpdateRoleAction extends Action
{
    public function execute(RoleDTO $roleDTO) : Role
    {
        $data = [
            'name' => $roleDTO->name,
            'description' => $roleDTO->description,
        ];

        $data = array_filter($data, fn($value) => !is_null($value));

        if ($roleDTO->role->class != $roleDTO->class)
        {
            $data['class'] = $roleDTO->class;
        }

        $roleDTO->permission->update($data);

        return $roleDTO->role->refresh();
    }
}