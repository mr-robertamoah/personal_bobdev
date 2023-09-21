<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\Exceptions\ProjectException;
use MrRobertAmoah\DTO\BaseDTO;

class EnsureProjectExistsAction extends Action
{
    public function execute(BaseDTO $dto)
    {
        if ($dto->project) {
            return;
        }
        
        throw new ProjectException('Sorry! A valid project is required to perform this action.');
    }
}