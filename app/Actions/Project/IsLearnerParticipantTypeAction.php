<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\Enums\ProjectParticipantEnum;

class IsLearnerParticipantTypeAction extends Action
{
    public function execute(string $participantType): bool
    {
        return in_array(
            strtoupper($participantType), 
            ProjectParticipantEnum::LEARNERALIASES
        );
    }
}