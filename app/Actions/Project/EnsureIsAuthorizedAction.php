<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Enums\ProjectParticipantEnum;
use App\Exceptions\ProjectException;
use App\Services\ProjectService;

class EnsureIsAuthorizedAction extends Action
{
    public function execute(ProjectDTO $projectDTO, string $action = 'create')
    {
        if ($this->isAuthorized($projectDTO, $action)) {
            return;
        }
        
        throw new ProjectException(
            $this->setErrorMessage($action)
        );
    }

    private function setErrorMessage(string $action): string
    {
        return match ($action) {
            'update' => "Sorry! You are not authorized to update a project.",
            'delete' => 'Sorry! You are not authorized to delete a project.',
            'create' => "Sorry! You are not authorized to create a project.",
        };
    }

    private function isAuthorized(ProjectDTO $projectDTO, string $action)
    {
        if (in_array($action, ['create'])) {
            return $projectDTO->addedby->hasUserTypes(ProjectService::AUTHORIZEDUSERTYPES);            
        }

        if (in_array($action, ['update', 'delete'])) {
            return $projectDTO->addedby->is($projectDTO->project?->addedby) || 
                $projectDTO->addedby->isAdmin() ||
                (
                    $projectDTO->project?->isFacilitator($projectDTO->addedby) && 
                    IsLearnerParticipantTypeAction::make()->execute($projectDTO->participantType)
                );
        }

        return false;
    }

    private function isNotAuthorized(ProjectDTO $projectDTO, string $action = 'create')
    {
        return !$this->isAuthorized(
            projectDTO: $projectDTO, action: $action
        );
    }
}