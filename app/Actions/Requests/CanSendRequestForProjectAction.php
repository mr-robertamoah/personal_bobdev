<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\DTOs\RequestDTO;
use App\Enums\ProjectParticipantEnum;
use App\Exceptions\RequestException;

class CanSendRequestForProjectAction extends Action
{
    public function execute(RequestDTO $requestDTO)
    {
        if (
            $requestDTO->purpose == ProjectParticipantEnum::facilitator->value &&
            !$requestDTO->from->isFacilitator()
        ) {
            throw new RequestException("Sorry, you need to be a facilitator to request to be a facilitator in a project");
        }

        if (
            $requestDTO->purpose == ProjectParticipantEnum::learner->value &&
            !$requestDTO->from->isStudent()
        ) {
            throw new RequestException("Sorry, you need to be a student to request to be a learner in a project");
        }
    }
}