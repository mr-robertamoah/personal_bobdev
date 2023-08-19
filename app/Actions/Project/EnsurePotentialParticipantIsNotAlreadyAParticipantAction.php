<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Enums\ProjectParticipantEnum;
use App\Exceptions\ProjectException;

class EnsurePotentialParticipantIsNotAlreadyAParticipantAction extends Action
{
    public function execute(ProjectDTO $projectDTO)
    {
        if (
            $projectDTO->project->isNotParticipant($projectDTO->participant) || 
            (
                $projectDTO->project->isSponsor($projectDTO->participant) && 
                strtoupper($projectDTO->participationType) !== ProjectParticipantEnum::sponsor->value
            )
        ) {
            return;
        }

        throw new ProjectException("Sorry! User is already participating in this project.");
    }
}