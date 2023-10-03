<?php

namespace App\Actions\Permission;
use App\Actions\Action;
use App\DTOs\PermissionDTO;
use App\Models\Permission;

class UpdatePermissionAction extends Action
{
    public function execute(PermissionDTO $permissionDTO) : Permission
    {
        $data = [
            'name' => $permissionDTO->name,
            'description' => $permissionDTO->description,
            'public' => $permissionDTO->public,
        ];

        $data = array_filter($data, fn($value) => !is_null($value));

        if ($permissionDTO->permission->class != $permissionDTO->class)
        {
            $data['class'] = $permissionDTO->class;
        }

        $permissionDTO->permission->update($data);

        return $permissionDTO->permission->refresh();
    }
}