<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Exceptions\ProjectException;

class CheckDataAppropriatenessAction extends Action
{
    public function execute(ProjectDTO $projectDTO, string $action = 'create')
    {
        if ($this->hasAppropriateData($projectDTO, $action)) {
            return;
        }
        
        throw new ProjectException(
            $this->setErrorMessage($action)
        );
    }

    private function setErrorMessage(string $action): string
    {
        return match ($action) {
            'update' => "Sorry! A name or description is required.",
            'delete' => 'Sorry! A valid project is required to perform this action.',
            'create' => "Sorry! The name and description of the project are required.",
        };
    }

    private function hasAppropriateData(ProjectDTO $projectDTO, string $action = 'create')
    {
        if ($action == 'create') {
            return (!is_null($projectDTO->name) && strlen($projectDTO->name) > 0) && 
                (!is_null($projectDTO->description) && strlen($projectDTO->description) > 0);
        }

        if ($action == 'update') {
            return ((!is_null($projectDTO->name) && strlen($projectDTO->name) > 0) || 
                (!is_null($projectDTO->description) && strlen($projectDTO->description) > 0));
        }
        
        return !is_null($projectDTO->projectId) && !is_null($projectDTO->project);
    }

    private function doesntHaveAppropriateData(ProjectDTO $projectDTO, string $action = 'create')
    {
        return !$this->hasAppropriateData($projectDTO, $action);
    }
}