<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\Actions\EnsureIsValidArrayAction;
use App\DTOs\ProjectDTO;
use App\DTOs\RequestableArrayValidationDTO;
use App\Exceptions\ProjectException;

class EnsureParticipationIsValidArrayAction extends Action
{
    public function execute(ProjectDTO $projectDTO)
    {
        EnsureIsValidArrayAction::make()->execute(
            RequestableArrayValidationDTO::new()->fromArray([
                "items" => $projectDTO->participations,
                "itemsName" => "participation",
                "exception" => ProjectException::class
            ])
        );
    }
}