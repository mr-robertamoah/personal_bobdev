<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\Enums\ProjectParticipantEnum;

class IsProjectType extends Action
{
    public function execute(string $type): bool
    {
        return in_array(strtoupper($type), 
            [...ProjectParticipantEnum::values(), 'LEARNER']
        );
    }
}