<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Exceptions\ProjectException;
use App\Models\Company;
use App\Models\User;

class RemoveParticipantAction extends Action
{
    public function execute(ProjectDTO $projectDTO, string $participationType)
    {
        $projectParticipantModel = $projectDTO->project
            ->getProjectParticipant($projectDTO->participant, $participationType);

        if (is_null($projectParticipantModel))
        {
            $class = $projectDTO->participant::class == User::class ? 'user' : 'company';
            throw new ProjectException("Sorry! Please {$class} with name {$projectDTO->participant->name} does not participate in {$projectDTO->project->name} project as {$participationType}.");
        }

        $projectParticipantModel->delete();
    }
}