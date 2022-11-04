<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\DTOs\RequestDTO;
use App\Exceptions\RequestException;
use App\Models\Company;
use App\Models\Project;

class CheckIfCanSendRequestAction extends Action
{
    public function execute(RequestDTO $requestDTO)
    {
        if ($requestDTO->for::class === Company::class) {
            CanSendRequestForCompanyAction::make()->execute($requestDTO); //TODO roll out what happens here
        }
        
        if ($requestDTO->for::class === Project::class) {
            CanSendRequestForProjectAction::make()->execute($requestDTO); 
        }

        throw new RequestException("Sorry, these set of data for this reques does not meet any requirement.");
    }
}