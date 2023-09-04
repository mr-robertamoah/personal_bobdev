<?php

namespace App\Actions;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Exceptions\ProjectException;
use MrRobertAmoah\DTO\BaseDTO;

class EnsureAddedByExistsAction extends Action
{
    public function execute(BaseDTO $dto)
    {
        if ($dto->addedby) {
            return;
        }
        
        throw new ProjectException('Sorry! A valid user is required to perform this action.');
    }
}