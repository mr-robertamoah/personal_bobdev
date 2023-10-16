<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Models\Project;

class GetProjectAction extends Action
{
    public function execute(ProjectDTO $projectDTO) : Project
    {
        return $projectDTO->project
            ->with([
                "participants", "addedby", "projectSessions", "skills",
            ])->first();
    }
}