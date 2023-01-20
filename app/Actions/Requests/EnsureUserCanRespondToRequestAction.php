<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\Actions\IsUserAnOfficialOfModelAction;
use App\Actions\IsUserAnOwnerOfModelAction;
use App\DTOs\ResponseDTO;
use App\Exceptions\ResponseException;
use App\Models\User;

class EnsureUserCanRespondToRequestAction extends Action
{
    public function execute(ResponseDTO $respondDTO)
    {
        if (
            $respondDTO->user->isAdmin() ||
            $this->canUserRespondToRequest($respondDTO)
        ) {
            return;
        }

        throw new ResponseException("Sorry, you are not authorized to respond to this request.");
    }

    private function canUserRespondToRequest(ResponseDTO $respondDTO): bool
    {
        if (
            $respondDTO->user->is($respondDTO->request->to) ||
            IsUserAnOfficialOfModelAction::make()->execute($respondDTO->user, $respondDTO->request->to)
        ) {
            return true;
        }

        if (
            $this->isRequestToAnOfficial($respondDTO) &&
            IsUserAnOwnerOfModelAction::make()->execute($respondDTO->user, $respondDTO->request->for)
        ) {
            return true;
        }

        return false;
    }

    private function isRequestToAnOfficial(ResponseDTO $respondDTO)
    {
        return $respondDTO->request->to::class == User::class &&
            (
                IsUserAnOfficialOfModelAction::make()->execute($respondDTO->request->to, $respondDTO->request->for) ||
                IsToFacilitatorAndHasALearnerPurpose::make()->execute($respondDTO->request)
            );
    }
}