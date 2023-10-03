<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

trait AuthorizationDTOTrait
{
    public ?Permission $permission = null;
    public ?Role $role = null;
    public ?User $user = null;
    public ?string $name = null;
    public ?string $like = null;
    public ?string $description = null;
    public ?string $class = null;
    public string|int|null $userId = null;
    public string|int|null $permissionId = null;
    public array $permissionIds = [];
    public string|int|null $roleId = null;
    public bool|null $public = true;
    public int|string|null $page = null;

    public function isForNextPage() : bool
    {
        return !is_null($this->page);
    }
}