<?php

namespace App\Actions\Authorization;

use App\Actions\Action;
use App\DTOs\AuthorizationDTO;
use App\Enums\PermissionEnum;
use App\Exceptions\AuthorizationException;

class EnsureUserCanGetAuthorizationsAction extends Action
{
    public function execute(AuthorizationDTO $authorizationDTO)
    {
        if (
            $authorizationDTO->user->isAdmin() ||
            $authorizationDTO->isForNextPage() ||
            $authorizationDTO->user->is($authorizationDTO->authorizable->owner) ||
            $authorizationDTO->authorizable->isOfficial($authorizationDTO->user) ||
            $authorizationDTO->user->isAuthorizedFor(
                $authorizationDTO->authorizable,
                names: [
                    PermissionEnum::ASSIGNAUTHORIZATIONS->value,
                    PermissionEnum::REMOVEAUTHORIZATIONS->value
                ]
            )
        ) return;

        $classBasename = class_basename($authorizationDTO->authorizable);
        throw new AuthorizationException("Sorry! You are not authorized to get the authorizations associated with {$authorizationDTO->authorizable->name} {$classBasename}.", 422);
    }
}