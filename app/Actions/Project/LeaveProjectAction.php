<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Exceptions\ProjectException;
use App\Models\User;

class LeaveProjectAction extends Action
{
    public function execute(ProjectDTO $projectDTO)
    {
        $projectParticipantModel = $projectDTO->project
            ->getProjectParticipant($projectDTO->participant, $projectDTO->participationType);

        if (is_null($projectParticipantModel))
        {
            $class = $projectDTO->participant::class == User::class ? 'user' : 'company';
            throw new ProjectException("Sorry! Please you cannot leave {$projectDTO->project->name} project as {$projectDTO->participationType}.");
        }

        $projectParticipantModel->delete();
    }
}