<?php

namespace App\Actions\ProjectSession;

use App\Actions\Action;
use App\DTOs\ProjectSessionDTO;
use App\Enums\PermissionEnum;
use App\Exceptions\ProjectSessionException;

class EnsureAddedbyCanDeleteProjectSessionAction extends Action
{
    public function execute(ProjectSessionDTO $projectSessionDTO)
    {
        if (
            $projectSessionDTO->addedby->isAdmin() ||
            $projectSessionDTO->addedby->isAuthorizedFor(
                authorizable: $projectSessionDTO->projectSession->project,
                name: PermissionEnum::MANAGEPROJECTSESSIONS->value
            ) ||
            $projectSessionDTO->projectSession->user->is($projectSessionDTO->addedby) ||
            $projectSessionDTO->projectSession->project->isOfficial($projectSessionDTO->addedby)
        ) return;
        
        throw new ProjectSessionException("Sorry! You are not authorized to perform this action on the project session with {$projectSessionDTO->projectSession->name} name.", 422);
    }
}