<?php

namespace App\Actions\Role;

use App\Actions\Action;
use App\Actions\BuildGetAuthorizationQueryAction;
use App\DTOs\RoleDTO;
use App\Enums\PaginationEnum;
use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

class GetRolesAction extends Action
{
    public function execute(RoleDTO $roleDTO) : LengthAwarePaginator
    {
        $query = BuildGetAuthorizationQueryAction::make()->execute(Role::query(), $roleDTO);

        if ($roleDTO->permissionName) $query->wherePermissionName($roleDTO->permissionName);

        return $query->paginate(PaginationEnum::getAuthorizations->value);
    }
}