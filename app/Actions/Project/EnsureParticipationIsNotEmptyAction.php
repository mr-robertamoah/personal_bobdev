<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\Actions\EnsureIsValidArrayAction;
use App\DTOs\ProjectDTO;
use App\DTOs\RequestableArrayValidationDTO;
use App\Exceptions\ProjectException;

class EnsureParticipationIsNotEmptyAction extends Action
{
    public function execute(ProjectDTO $projectDTO)
    {
        if (count($projectDTO->participations))
        {
            return;
        }

        throw new ProjectException("Sorry, no participant was provided.");
    }
}