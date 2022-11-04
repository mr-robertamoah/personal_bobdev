<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\DTOs\ResponseDTO;
use App\Exceptions\RespondException;
use App\Models\Company;
use App\Models\Project;

class CheckIfCanRespondToRequestAction extends Action
{
    public function execute(ResponseDTO $respondDTO)
    {
        if ($respondDTO->user->is($respondDTO->request->to)) {
            return;
        }

        if (
            $respondDTO->request->to::class == Project::class && 
            (
                $respondDTO->request->to->addedby->is($respondDTO->user)
            )
        ) {
            return;
        }

        if (
            $respondDTO->request->to::class == Company::class && 
            (
                $respondDTO->request->to->owner->is($respondDTO->user) ||
                $respondDTO->request->to->isManager($respondDTO->user)
            )
        ) {
            return;
        }

        if ($respondDTO->user->isAdmin()) {
            return;
        }

        throw new RespondException("Sorry, you are not authorized to respond to this request.");
    }
}