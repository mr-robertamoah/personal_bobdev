<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\Actions\Company\BecomeCompanyMemberAction;
use App\DTOs\ResponseDTO;

class CompanyResponseAction extends Action
{
    public function execute(ResponseDTO $responseDTO)
    {
        $potentialParticipant = GetPotentialParticipantFromRequestAction::make()
            ->execute($responseDTO->request);
        
        BecomeCompanyMemberAction::make()->execute(
            $responseDTO->request->for,
            $potentialParticipant,
            $responseDTO->request->purpose
        );
    }
}