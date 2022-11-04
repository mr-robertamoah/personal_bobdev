<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Enums\ProjectParticipantEnum;
use App\Exceptions\ProjectException;

class CheckIfValidParticipantAction extends Action
{
    public function execute(ProjectDTO $projectDTO)
    {
        if (!$projectDTO->participant) {
            throw new ProjectException("Sorry, no participant was provided.");
        }

        $participantType = strtoupper($projectDTO->participantType);

        if (!in_array($participantType, ProjectParticipantEnum::values())) {
            throw new ProjectException("Sorry, participant cannot participate in the project as {$participantType}.");
        }

        $method = "is" . ucfirst(strtolower($projectDTO->participantType));

        if ($projectDTO->participant->$method()) {
            return;
        }

        $participantType = strtolower($projectDTO->participantType);
        throw new ProjectException("Sorry, participant is not a {$participantType}");
    }
}