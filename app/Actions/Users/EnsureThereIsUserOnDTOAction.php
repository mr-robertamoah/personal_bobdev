<?php

namespace App\Actions\Users;

use App\Actions\Action;
use App\Exceptions\UserException;
use MrRobertAmoah\DTO\BaseDTO;

class EnsureThereIsUserOnDTOAction extends Action
{
    public function execute(BaseDTO $dto, string $propertyName)
    {
        if (!is_null($dto->$propertyName)) {
            return;
        }

        throw new UserException("Sorry! User is required.");
    }
}