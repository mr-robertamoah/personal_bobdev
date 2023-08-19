<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\Enums\ProjectParticipantEnum;
use App\Enums\RequestTypeEnum;

class IsLearnerParticipantTypeAction extends Action
{
    public function execute(string $participationType): bool
    {
        return in_array(
            strtoupper($participationType), 
            RequestTypeEnum::learnerAliases()
        );
    }
}