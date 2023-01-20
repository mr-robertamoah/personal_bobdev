<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Enums\ProjectParticipantEnum;

class BecomeSponsorOfProjectAction extends Action
{
    public function execute(ProjectDTO $projectDTO)
    {
        $projectDTO->participant->projects()->create([
            'participating_as' => ProjectParticipantEnum::sponsor->value,
            'project_id' => $projectDTO->project->id
        ]);

        return $projectDTO->project->refresh();
    }
}