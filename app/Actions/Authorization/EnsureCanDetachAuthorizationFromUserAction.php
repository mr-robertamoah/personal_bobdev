<?php

namespace App\Actions\Authorization;

use App\Actions\Action;
use App\DTOs\AuthorizationDTO;
use App\Enums\PermissionEnum;
use App\Exceptions\AuthorizationException;
use App\Models\Authorization;

class EnsureCanDetachAuthorizationFromUserAction extends Action
{
    public function execute(AuthorizationDTO $dto)
    {
        if (
            $dto->user->isAdmin() ||
            $dto->mainAuthorization->user->is($dto->user) ||
            $dto->mainAuthorization->authorized->is($dto->user) ||
            $dto->mainAuthorization->authorizable->owner->is($dto->user) ||
            $dto->user->isAuthorizedFor(
                authorizable: $dto->mainAuthorization->authorizable,
                    name: PermissionEnum::REMOVEAUTHORIZATIONS->value)
        ) return;

        throw new AuthorizationException("Sorry! You are not authorized to remove authorization with name {$dto->mainAuthorization->authorization->name} from {$dto->mainAuthorization->authorized->name}.", 422);
    }
}