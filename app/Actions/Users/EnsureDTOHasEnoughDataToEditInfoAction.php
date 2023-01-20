<?php

namespace App\Actions\Users;

use App\Actions\Action;
use App\DTOs\UserDTO;
use App\Exceptions\UserException;

class EnsureDTOHasEnoughDataToEditInfoAction extends Action
{
    public function execute(UserDTO $userDTO)
    {
        if (
            $userDTO->firstName || $userDTO->surname || $userDTO->otherNames || 
            $userDTO->email || $userDTO->gender || $userDTO->dob
        ) {
            return;
        }

        throw new UserException("Sorry, you do not have enough data to perform this action.");
    }
}