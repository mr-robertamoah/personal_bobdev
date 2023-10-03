<?php

namespace App\Actions\Permission;

use App\Actions\Action;
use App\Actions\BuildGetAuthorizationQueryAction;
use App\DTOs\PermissionDTO;
use App\Enums\PaginationEnum;
use App\Models\Authorization;
use App\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;

class GetPermissionsAction extends Action
{
    public function execute(PermissionDTO $permissionDTO) : LengthAwarePaginator
    {
        $query = BuildGetAuthorizationQueryAction::make()->execute(Permission::query(), $permissionDTO);

        return $query->paginate(PaginationEnum::getAuthorizations->value);
    }
}