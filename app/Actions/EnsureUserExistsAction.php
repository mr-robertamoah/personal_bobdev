<?php

namespace App\Actions;

use App\Actions\Action;
use App\Exceptions\ServiceException;
use MrRobertAmoah\DTO\BaseDTO;

class EnsureUserExistsAction extends Action
{
    public function execute(BaseDTO $dto, ?string $property = "addedby")
    {
        if ($dto->$property) {
            return;
        }
        
        throw new ServiceException('Sorry! A valid user is required to perform this action.', 422);
    }
}