<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\Actions\Activity\AddActivityAction;
use App\Actions\Company\BecomeCompanyMemberAction;
use App\DTOs\ActivityDTO;
use App\DTOs\ResponseDTO;

class CompanyResponseAction extends Action
{
    public function execute(ResponseDTO $responseDTO)
    {
        $potentialMember = GetPotentialParticipantFromRequestAction::make()
            ->execute($responseDTO->request);
        
        BecomeCompanyMemberAction::make()->execute(
            $responseDTO->request->for,
            $potentialMember,
            $responseDTO->request->type
        );
    }
}