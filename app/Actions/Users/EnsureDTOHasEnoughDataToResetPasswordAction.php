<?php

namespace App\Actions\Users;

use App\Actions\Action;
use App\DTOs\UserDTO;
use App\Exceptions\UserException;

class EnsureDTOHasEnoughDataToResetPasswordAction extends Action
{
    public function execute(UserDTO $userDTO)
    {
        if ($userDTO->password) {
            return;
        }

        throw new UserException("Sorry, password is required to perform this action.");
    }
}