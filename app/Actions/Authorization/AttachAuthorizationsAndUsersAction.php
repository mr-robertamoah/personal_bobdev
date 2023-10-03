<?php

namespace App\Actions\Authorization;

use App\Actions\Action;
use App\DTOs\AuthorizationDTO;
use App\Models\Authorization;

class AttachAuthorizationsAndUsersAction extends Action
{
    public function execute(AuthorizationDTO $authorizationDTO) : Authorization
    {
        // TODO add sending request or attaching based on user settings
        $authorization = $authorizationDTO->user->authorizations()->create();

        $authorization->authorizable()->associate($authorizationDTO->authorizable);
        $authorization->authorized()->associate($authorizationDTO->authorized);
        $authorization->authorization()->associate($authorizationDTO->authorization);
        $authorization->save();

        return $authorization;
    }
}