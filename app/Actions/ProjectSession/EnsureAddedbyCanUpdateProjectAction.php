<?php

namespace App\Actions\ProjectSession;

use App\Actions\Action;
use App\Actions\Project\EnsureAddedbyIsAuthorizedAction;
use App\DTOs\ProjectDTO;
use App\DTOs\ProjectSessionDTO;
use App\Exceptions\ProjectSessionException;

class EnsureAddedbyCanUpdateProjectAction extends Action
{
    public function execute(ProjectSessionDTO $projectSessionDTO, ?string $what = null)
    {
        EnsureAddedbyIsAuthorizedAction::make()->execute(
            ProjectDTO::new()->fromArray([
                'addedby' => $projectSessionDTO->addedby,
                'project' => $projectSessionDTO->project,
            ]),
            'update', what: $what
        );
    }
}