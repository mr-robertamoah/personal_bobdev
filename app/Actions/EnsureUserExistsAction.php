<?php

namespace App\Actions;

use App\Actions\Action;
use App\Exceptions\ServiceException;
use MrRobertAmoah\DTO\BaseDTO;

class EnsureUserExistsAction extends Action
{
    public function execute(
        BaseDTO $dto, 
        ?string $property = "addedby",
        ?string $model = "user",
    )
    {
        if ($dto->$property) {
            return;
        }
        
        throw new ServiceException("Sorry! A valid {$model} is required to perform this action.", 422);
    }
}