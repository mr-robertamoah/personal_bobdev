<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Exceptions\ProjectException;

class EnsureProjectHasSkillsAction extends Action
{
    public function execute(ProjectDTO $projectDTO, array $skillIds)
    {
        $unattachedIds = array_diff($projectDTO->project->skills()->allRelatedIds()->toArray(), $skillIds);
        if (count($unattachedIds) == 0) {
            return;
        }
        
        throw new ProjectException("Sorry! The skills with ids {$unattachedIds} have not been added to the project and hence, cannot be removed.");
    }
}