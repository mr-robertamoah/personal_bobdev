<?php

namespace App\Actions\Permission;
use App\Actions\Action;
use App\DTOs\AuthorizationDTO;
use App\Exceptions\AuthorizationException;

class EnsureAuthorizationModelsExistAction extends Action
{
    public function execute(
        AuthorizationDTO $authorizationDTO,
        ?string $only = null
    )
    {
        if (
            (is_null($authorizationDTO->authorizable) && is_null($only)) ||
            ($only == "authorizable" && is_null($authorizationDTO->$only))
        ) {
            throw new AuthorizationException("Sorry! A Company/Project must be given to perform this action.", 422);
        }
        
        if (
            (is_null($authorizationDTO->authorized) && is_null($only)) ||
            ($only == "authorized" && is_null($authorizationDTO->$only))
        ) {
            throw new AuthorizationException("Sorry! The User to be authorized must be given to perform this action.", 422);
        }
        
        if (
            (is_null($authorizationDTO->authorization) && is_null($only)) ||
            ($only == "authorization" && is_null($authorizationDTO->$only))
        ) {
            throw new AuthorizationException("Sorry! A Role/Permission must be given to perform this action.", 422);
        }
    }
}