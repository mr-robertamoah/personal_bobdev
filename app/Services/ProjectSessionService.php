<?php

namespace App\Services;

use App\Actions\EnsureUserExistsAction;
use App\Actions\MakeDatesCarbonAction;
use App\Actions\Project\EnsureProjectExistsAction;
use App\Actions\ProjectSession\CreateProjectSessionAction;
use App\Actions\ProjectSession\EnsureAddedbyCanUpdateProjectAction;
use App\Actions\ProjectSession\EnsureDatesAreWithinProjectDatesAction;
use App\Actions\ProjectSession\EnsureProjectSessionExistsAction;
use App\Actions\ProjectSession\EnsureValidDataExistsAction;
use App\Actions\ProjectSession\SetEnumValuesAction;
use App\Actions\ProjectSession\UpdateProjectSessionAction;
use App\DTOs\ProjectSessionDTO;
use App\Models\Project;
use App\Models\ProjectSession;

class ProjectSessionService
{
    public function createProjectSession(ProjectSessionDTO $projectSessionDTO) : ProjectSession
    {
        EnsureUserExistsAction::make()->execute($projectSessionDTO);

        $projectSessionDTO = $projectSessionDTO->withProject(
            Project::find($projectSessionDTO->projectId)
        );

        EnsureProjectExistsAction::make()->execute($projectSessionDTO);

        EnsureAddedbyCanUpdateProjectAction::make()->execute($projectSessionDTO);

        $projectSessionDTO = SetEnumValuesAction::make()->execute($projectSessionDTO);

        EnsureValidDataExistsAction::make()->execute($projectSessionDTO);

        $projectSessionDTO = MakeDatesCarbonAction::make()->execute($projectSessionDTO);

        EnsureDatesAreWithinProjectDatesAction::make()->execute($projectSessionDTO);

        return CreateProjectSessionAction::make()->execute($projectSessionDTO);
    }
    
    public function updateProjectSession(ProjectSessionDTO $projectSessionDTO)
    {
        EnsureUserExistsAction::make()->execute($projectSessionDTO);

        $projectSessionDTO = $projectSessionDTO->withProjectSession(
            ProjectSession::find($projectSessionDTO->projectSessionId)
        );

        EnsureProjectSessionExistsAction::make()->execute($projectSessionDTO);

        EnsureAddedbyCanUpdateProjectAction::make()->execute($projectSessionDTO);

        $projectSessionDTO = SetEnumValuesAction::make()->execute($projectSessionDTO, true);

        EnsureValidDataExistsAction::make()->execute($projectSessionDTO, true);

        $projectSessionDTO = MakeDatesCarbonAction::make()->execute($projectSessionDTO);

        EnsureDatesAreWithinProjectDatesAction::make()->execute($projectSessionDTO);

        return UpdateProjectSessionAction::make()->execute($projectSessionDTO);
    }

    public function deleteProjectSession(ProjectSessionDTO $projectSessionDTO)
    {
        EnsureUserExistsAction::make()->execute($projectSessionDTO);

        $projectSessionDTO = $projectSessionDTO->withProjectSession(
            ProjectSession::find($projectSessionDTO->projectSessionId)
        );

        EnsureProjectSessionExistsAction::make()->execute($projectSessionDTO);

        EnsureAddedbyCanUpdateProjectAction::make()->execute($projectSessionDTO);
    }
}