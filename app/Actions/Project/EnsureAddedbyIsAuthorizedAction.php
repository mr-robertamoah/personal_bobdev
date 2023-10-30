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
            $this->setErrorMessage($action), 422
        );
    }

    private function setErrorMessage(string $action): string
    {
        return match ($action) {
            'update' => "Sorry! You are not authorized to update a project.",
            'delete' => 'Sorry! You are not authorized to delete a project.',
            'create' => "Sorry! You are not authorized to create a project.",
            default => "Sorry! You are not authorized to perform this action on this project.",
        };
    }

    private function isAuthorized(
        ProjectDTO $projectDTO,
        string $action,
        string $participationType = null,
        string $what = null,
    )
    {
        $isAddedby = $projectDTO->addedby->is($projectDTO->project?->addedby);
        $isAdmin = $projectDTO->addedby->isAdmin();

        if ($isAddedby || $isAdmin) return true;

        if (in_array($action, ['create']))
        {
            return $projectDTO->addedby->hasUserTypes(ProjectService::AUTHORIZEDUSERTYPES);            
        }

        $isFacilitator = $projectDTO->project?->isFacilitator($projectDTO->addedby);

        if (in_array($what, ['skills', 'project session']) && $isFacilitator)
        {
            return true;
        }

        if (
            in_array($action, ['remove']) &&
            ($isFacilitator)
        ) {
            return true;
        }

        if (in_array($action, ['update', 'delete']))
        {
            return (
                $isFacilitator && 
                IsLearnerParticipantTypeAction::make()->execute($participationType ?: $projectDTO->participationType)
            );
        }

        return false;
    }
}