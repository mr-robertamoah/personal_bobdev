<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Exceptions\ProjectException;

class EnsureProjectExistsAction extends Action
{
    public function execute(ProjectDTO $projectDTO)
    {
        if ($projectDTO->project) {
            return;
        }
        
        throw new ProjectException('Sorry! A valid project is required to perform this action.');
    }
}