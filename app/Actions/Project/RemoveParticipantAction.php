<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Exceptions\ProjectException;
use App\Models\Company;
use App\Models\User;

class RemoveParticipantAction extends Action
{
    public function execute(ProjectDTO $projectDTO, User|Company $participant)
    {
        
    }
}