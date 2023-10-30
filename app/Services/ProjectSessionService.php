<?php

namespace App\Services;

use App\Actions\EnsureUserExistsAction;
use App\Actions\GetModelFromDTOAction;
use App\Actions\MakeDatesCarbonAction;
use App\Actions\Project\EnsureProjectExistsAction;
use App\Actions\ProjectSession\CreateProjectSessionAction;
use App\Actions\ProjectSession\DeleteProjectSessionAction;
use App\Actions\ProjectSession\EnsureAddedbyCanDeleteProjectSessionAction;
use App\Actions\ProjectSession\EnsureAddedbyCanUpdateProjectAction;
use App\Actions\ProjectSession\EnsureAddedbyCanUpdateProjectSessionAction;
use App\Actions\ProjectSession\EnsureDatesAreWithinProjectDatesAction;
use App\Actions\ProjectSession\EnsureProjectSessionExistsAction;
use App\Actions\ProjectSession\EnsureValidDataExistsAction;
use App\Actions\ProjectSession\EnsureValidTimeDataAction;
use App\Actions\ProjectSession\SetEnumValuesAction;
use App\Actions\ProjectSession\UpdateProjectSessionAction;
use App\DTOs\ProjectSessionDTO;
use App\Models\Project;
use App\Models\ProjectSession;

class ProjectSessionService extends Service
{
    public function createProjectSession(ProjectSessionDTO $projectSessionDTO)
    {
        EnsureUserExistsAction::make()->execute($projectSessionDTO);

        $projectSessionDTO = $projectSessionDTO->withProject(
            GetModelFromDTOAction::make()->execute(
                $projectSessionDTO, "project", "project"
            )
        );

        EnsureProjectExistsAction::make()->execute($projectSessionDTO);

        EnsureAddedbyCanUpdateProjectAction::make()->execute(
            $projectSessionDTO, what: "project session"
        );

        $projectSessionDTO = SetEnumValuesAction::make()->execute($projectSessionDTO);

        EnsureValidDataExistsAction::make()->execute($projectSessionDTO);

        $projectSessionDTO = MakeDatesCarbonAction::make()->execute($projectSessionDTO, true);

        EnsureDatesAreWithinProjectDatesAction::make()->execute($projectSessionDTO);

        EnsureValidTimeDataAction::make()->execute($projectSessionDTO);

        return CreateProjectSessionAction::make()->execute($projectSessionDTO);
    }
    
    public function updateProjectSession(ProjectSessionDTO $projectSessionDTO)
    {
        EnsureUserExistsAction::make()->execute($projectSessionDTO);

        $projectSessionDTO = $projectSessionDTO->withProjectSession(
            GetModelFromDTOAction::make()->execute(
                $projectSessionDTO, "projectSession", "projectSession"
            )
        );

        EnsureProjectSessionExistsAction::make()->execute($projectSessionDTO);

        EnsureAddedbyCanUpdateProjectSessionAction::make()->execute($projectSessionDTO);

        $projectSessionDTO = SetEnumValuesAction::make()->execute($projectSessionDTO, true);

        EnsureValidDataExistsAction::make()->execute($projectSessionDTO, true);

        $projectSessionDTO = MakeDatesCarbonAction::make()->execute($projectSessionDTO, true, true);

        EnsureDatesAreWithinProjectDatesAction::make()->execute($projectSessionDTO);

        EnsureValidTimeDataAction::make()->execute($projectSessionDTO, true);

        return UpdateProjectSessionAction::make()->execute($projectSessionDTO);
    }

    public function deleteProjectSession(ProjectSessionDTO $projectSessionDTO) : bool
    {
        EnsureUserExistsAction::make()->execute($projectSessionDTO);

        $projectSessionDTO = $projectSessionDTO->withProjectSession(
            GetModelFromDTOAction::make()->execute(
                $projectSessionDTO, "projectSession", "projectSession"
            )
        );

        EnsureProjectSessionExistsAction::make()->execute($projectSessionDTO);

        EnsureAddedbyCanDeleteProjectSessionAction::make()->execute($projectSessionDTO);

        return DeleteProjectSessionAction::make()->execute($projectSessionDTO);
    }
}