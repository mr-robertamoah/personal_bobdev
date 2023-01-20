<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\Enums\ProjectParticipantEnum;

class isProjectPurpose extends Action
{
    public function execute(string $purpose): bool
    {
        return in_array(strtoupper($purpose), 
            [...ProjectParticipantEnum::values(), 'LEARNER']
        );
    }
}