<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Exceptions\ProjectException;

class EnsureFacilitatorCannotRemoveFacilitatorAction extends Action
{
    public function execute(ProjectDTO $projectDTO, string $participationType)
    {
        if ($projectDTO->project->isOfficial($projectDTO->addedby))
        {
            return;
        }

        if (
            $projectDTO->project->isNotFacilitator($projectDTO->addedby) ||
            strtolower($participationType) != 'facilitator'
        ) {
            return;
        }

        throw new ProjectException("Sorry! As a facilitator, you cannot remove another facilitator. Inform the Owner/Official of this project");
    }
}