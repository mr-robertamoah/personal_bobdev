<?php

namespace App\Actions;

use App\DTOs\CompanyDTO;
use App\DTOs\ProjectDTO;
use Illuminate\Database\Eloquent\Builder;

class BuildGetAuthorizableQueryAction extends Action
{
    public function execute(Builder $query, CompanyDTO|ProjectDTO $dto) : Builder
    {
        if ($dto->name) $query->whereNameIsLike($dto->name);
        if ($dto->owner) $query->whereIsOwnedBy($dto->owner);
        if ($dto->like) $query->whereIsLike($dto->like);
        if ($dto->official) $query->whereIsOfficial($dto->official);
        if ($dto->member) $query->whereIsMember($dto->member);

        $query->latest();

        return $query;
    }
}