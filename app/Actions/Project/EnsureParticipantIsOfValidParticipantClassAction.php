<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Exceptions\ProjectException;
use App\Models\ProjectParticipant;

class EnsureParticipantIsOfValidParticipantClassAction extends Action
{
    public function execute(ProjectDTO $projectDTO)
    {
        if (in_array($projectDTO->participant::class,ProjectParticipant::$validParticipantClasses))
        {
            return;
        }

        throw new ProjectException("Sorry! Each participant provided must either be a User or Company.");
    }
}