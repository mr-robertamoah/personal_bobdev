<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Enums\ProjectParticipantEnum;
use App\Exceptions\ProjectException;

class BecomeProjectParticipantAction extends Action
{
    public function execute(ProjectDTO $projectDTO, ?string $participationType)
    {
        $participationType = $participationType ?? $projectDTO->participationType;
    
        if (is_null($participationType))
        {
            throw new ProjectException("Sorry! No participation type (facilitator, learner/student, sponsor) found.");
        }

        $action = $this->getAppropriateAction(strtoupper($participationType));

        $action->execute($projectDTO);
    }

    private function getAppropriateAction(string $participationType)
    {
        return match ($participationType) {
            ProjectParticipantEnum::facilitator->value => 
                BecomeFacilitatorOfProjectAction::make(),
            ProjectParticipantEnum::learner->value => 
                BecomeLearnerOfProjectAction::make(),
            'LEARNER' => 
                BecomeLearnerOfProjectAction::make(),
            ProjectParticipantEnum::sponsor->value => 
                BecomeSponsorOfProjectAction::make(),
            default =>
                throw new ProjectException("Sorry! {$participationType} cannot be used to perform this action.")
        };
    }
}