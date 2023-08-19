<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Exceptions\ProjectException;

class EnsurePotentialParticipantExistsAction extends Action
{
    public function execute(ProjectDTO $projectDTO)
    {
        if ($projectDTO->participant) {
            return;
        }
        
        throw new ProjectException("Sorry, no participant was provided.");
    }
}