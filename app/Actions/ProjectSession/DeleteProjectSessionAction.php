<?php

namespace App\Actions\ProjectSession;

use App\Actions\Action;
use App\DTOs\ProjectSessionDTO;
use App\Exceptions\ProjectSessionException;

class DeleteProjectSessionAction extends Action
{
    public function execute(ProjectSessionDTO $projectSessionDTO) : bool
    {
        return $projectSessionDTO->projectSession->delete();
    }
}