<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Exceptions\ProjectException;

class EnsureProjectDoesNotHaveSkillsAction extends Action
{
    public function execute(ProjectDTO $projectDTO, array $skillIds)
    {
        $attachedIds = array_intersect($projectDTO->project->skills()->allRelatedIds()->toArray(), $skillIds);
        if (count($attachedIds) == 0) {
            return;
        }
        
        throw new ProjectException("Sorry! The skills with ids {$attachedIds} have already been added to this project. Select only skills that have not been added.");
    }
}