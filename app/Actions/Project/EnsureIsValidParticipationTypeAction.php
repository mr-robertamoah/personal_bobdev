<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Enums\ProjectParticipantEnum;
use App\Exceptions\ProjectException;

class EnsureIsValidParticipationTypeAction extends Action
{
    public function execute(ProjectDTO $projectDTO, ?string $participationType=null)
    {
        $participationType = strtoupper($participationType ?? $projectDTO->participationType);

        if (!in_array($participationType, [...ProjectParticipantEnum::values(), 'LEARNER'])) {
            throw new ProjectException("Sorry, participant cannot participate in the project as {$participationType}.");
        }

        $method = "is" . ucfirst(strtolower($participationType));

        if ($projectDTO->participant->$method()) {
            return;
        }

        $participationType = strtolower($participationType);
        throw new ProjectException("Sorry, participant is not a {$participationType}");
    }
}