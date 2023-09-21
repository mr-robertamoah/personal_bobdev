<?php

namespace App\Actions\ProjectSession;

use App\Actions\Action;
use App\DTOs\ProjectSessionDTO;
use App\Exceptions\ProjectSessionException;

class EnsureProjectSessionExistsAction extends Action
{
    public function execute(ProjectSessionDTO $projectSessionDTO)
    {
        if ($projectSessionDTO->projectSession) {
            return;
        }
        
        throw new ProjectSessionException('Sorry! A valid project session is required to perform this action.');
    }
}