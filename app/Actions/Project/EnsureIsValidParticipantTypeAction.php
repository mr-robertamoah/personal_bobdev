<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Enums\ProjectParticipantEnum;
use App\Exceptions\ProjectException;

class EnsureIsValidParticipantTypeAction extends Action
{
    public function execute(ProjectDTO $projectDTO)
    {
        $participantType = strtoupper($projectDTO->participantType);

        if (!in_array($participantType, [...ProjectParticipantEnum::values(), 'LEARNER'])) {
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