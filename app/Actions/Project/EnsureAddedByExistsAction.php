<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Exceptions\ProjectException;

class EnsureAddedByExistsAction extends Action
{
    public function execute(ProjectDTO $projectDTO)
    {
        if ($projectDTO->addedby) {
            return;
        }
        
        throw new ProjectException('Sorry! A valid user is required to perform this action.');
    }
}