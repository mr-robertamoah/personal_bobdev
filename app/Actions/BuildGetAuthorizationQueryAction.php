<?php

namespace App\Actions;

use App\DTOs\PermissionDTO;
use App\DTOs\RoleDTO;
use Illuminate\Database\Eloquent\Builder;

class BuildGetAuthorizationQueryAction extends Action
{
    public function execute(Builder $query, RoleDTO|PermissionDTO $dto) : Builder
    {
        $query->wherePublic();

        if ($dto->user->isAdmin()) $query->orWherePrivate();
        else $query->orWhereCreatedBy($dto->user);

        if ($dto->name) $query->whereName($dto->name);
        if ($dto->like) $query->whereIsLike($dto->like);
        if ($dto->class) $query->whereClass($dto->class);

        $query->latest();

        return $query;
    }
}