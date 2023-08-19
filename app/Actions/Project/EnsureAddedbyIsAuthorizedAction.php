<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use App\Exceptions\ProjectException;
use App\Services\ProjectService;

class EnsureAddedbyIsAuthorizedAction extends Action
{
    public function execute(
        ProjectDTO $projectDTO, 
        string $action = 'create', 
        string $participationType = null,
        string $what = null,
    )
    {
        if ($this->isAuthorized(
            $projectDTO,
            $action,
            $participationType,
            $what
        )) {
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

    private function isAuthorized(
        ProjectDTO $projectDTO,
        string $action,
        string $participationType = null,
        string $what = null,
    )
    {
        if (in_array($action, ['create'])) {
            return $projectDTO->addedby->hasUserTypes(ProjectService::AUTHORIZEDUSERTYPES);            
        }

        if (
            in_array($what, ['skills']) &&
            $isFacilitator = $projectDTO->project?->isFacilitator($projectDTO->addedby)
        ) {
            return true;
        }

        if (
            in_array($action, ['remove']) &&
            (
                $isAdmin = $projectDTO->addedby->isAdmin() ||
                $isAddedby = $projectDTO->addedby->is($projectDTO->project?->addedby)
            )
        ) {
            return true;
        }

        if (in_array($action, ['update', 'delete'])) {

            return $isAddedby || 
                $isAdmin ||
                (
                    $isFacilitator && 
                    IsLearnerParticipantTypeAction::make()->execute($participationType ?? $projectDTO->participationType)
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