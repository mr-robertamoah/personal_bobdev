<?php

namespace App\Services;

use App\Actions\EnsureAddedByExistsAction;
use App\Actions\ProjectSession\EnsureProjectSessionExistsAction;
use App\DTOs\ProjectSessionDTO;
use App\Models\ProjectSession;

class ProjectSessionService
{
    public function createProjectSession(ProjectSessionDTO $projectSessionDTO)
    {
        EnsureAddedByExistsAction::make()->execute($projectSessionDTO);

        $projectSessionDTO = $projectSessionDTO->withProjectSession(
            ProjectSession::find($projectSessionDTO->projectSessionId)
        );

        EnsureValidDataExistsAction::make()->execute($projectDTO);

        CreateProjectSessionAction::make()->execute($projectDTO);
    }
    
    public function updateProjectSession(ProjectSessionDTO $projectSessionDTO)
    {
        EnsureAddedByExistsAction::make()->execute($projectSessionDTO);

        $projectSessionDTO = $projectSessionDTO->withProjectSession(
            ProjectSession::find($projectSessionDTO->projectSessionId)
        );

        EnsureProjectSessionExistsAction::make()->execute($projectSessionDTO);

    }
}