<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Enums\ProjectParticipantEnum;
use App\Exceptions\ProjectException;

class BecomeProjectParticipantAction extends Action
{
    public function execute(ProjectDTO $projectDTO)
    {
        $action = $this->getAppropriateAction(strtoupper($projectDTO->participantType));

        $action->execute($projectDTO);
    }

    private function getAppropriateAction(string $participantType)
    {
        return match ($participantType) {
            ProjectParticipantEnum::facilitator->value => 
                BecomeFacilitatorOfProjectAction::make(),
            ProjectParticipantEnum::learner->value => 
                BecomeLearnerOfProjectAction::make(),
            'LEARNER' => 
                BecomeLearnerOfProjectAction::make(),
            ProjectParticipantEnum::sponsor->value => 
                BecomeSponsorOfProjectAction::make(),
            default =>
                throw new ProjectException("Sorry! {$participantType} cannot be used to perform this action.")
        };
    }
}