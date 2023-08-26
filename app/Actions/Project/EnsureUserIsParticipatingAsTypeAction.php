<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Exceptions\ProjectException;

class EnsureUserIsParticipatingAsTypeAction extends Action
{
    public function execute(ProjectDTO $projectDTO, ?string $participationType=null)
    {
        $participationType = $participationType ?? $projectDTO->participationType;
        if (
            $projectDTO->project->isParticipantType(
                $projectDTO->participant,
                $participationType
        )) {
            return;
        }

        throw new ProjectException("Sorry! User with name {$projectDTO->participant->name} is not participating as {$participationType} in this project.");
    }
}