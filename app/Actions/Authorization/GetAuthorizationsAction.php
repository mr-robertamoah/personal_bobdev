<?php

namespace App\Actions\Authorization;

use App\Actions\Action;
use App\DTOs\AuthorizationDTO;
use App\Enums\PaginationEnum;
use App\Models\Authorization;
use Illuminate\Pagination\LengthAwarePaginator;

class GetAuthorizationsAction extends Action
{
    public function execute(AuthorizationDTO $authorizationDTO) : LengthAwarePaginator
    {
        $query = Authorization::query()->latest();

        if ($authorizationDTO->authorizable) $query->whereAuthorizable($authorizationDTO->authorizable);
        if ($authorizationDTO->authorized) $query->whereAuthorized($authorizationDTO->authorized);
        if ($authorizationDTO->authorization) $query->whereAuthorization($authorizationDTO->authorization);
        if ($authorizationDTO->name) $query->whereAuthorizationName($authorizationDTO->name);

        return $query->paginate(PaginationEnum::getAuthorizations->value);
    }
}