<?php

namespace App\Actions\Authorization;

use App\Actions\Action;
use App\DTOs\AuthorizationDTO;
use App\Exceptions\AuthorizationException;

class EnsureAuthorizationExistsAction extends Action
{
    public function execute(AuthorizationDTO $dto)
    {
        if ($dto->mainAuthorization) {
            return;
        }
        
        throw new AuthorizationException('Sorry! A valid authorization is required to perform this action.', 422);
    }
}