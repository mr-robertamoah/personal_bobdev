<?php

namespace App\Actions\ProjectSession;

use App\Actions\Action;
use App\DTOs\ProjectSessionDTO;
use App\Exceptions\ProjectSessionException;

class EnsureValidDataExistsAction extends Action
{
    public function execute(ProjectSessionDTO $projectSessionDTO)
    {
        if ($projectSessionDTO->project) {
            throw new ProjectSessionException('', 422);
        }
    }
}