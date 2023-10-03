<?php

namespace App\Actions\Authorization;

use App\Actions\Action;
use App\DTOs\AuthorizationDTO;
use App\Models\Authorization;

class DetachAuthorizationsAndUsersAction extends Action
{
    public function execute(AuthorizationDTO $authorizationDTO) : bool
    {
        return $authorizationDTO->mainAuthorization->delete();
    }
}