<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\Actions\IsUserAnOfficialOfModelAction;
use App\Actions\Project\BecomeProjectParticipantAction;
use App\DTOs\ProjectDTO;
use App\DTOs\ResponseDTO;
use App\Models\Request;

class ProjectResponseAction extends Action
{
    public function execute(ResponseDTO $responseDTO)
    {
        $potentialParticipant = GetPotentialParticipantFromRequestAction::make()
            ->execute($responseDTO->request);

        $projectDTO = ProjectDTO::new()->fromArray([
            'project' => $responseDTO->request->for,
            'participant' => $potentialParticipant,
            'participantType' => $responseDTO->request->purpose
        ]);

        BecomeProjectParticipantAction::make()->execute($projectDTO);
    }
}