<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\Actions\Users\IsUserTypeAction;
use App\DTOs\RequestDTO;
use App\Exceptions\RequestException;
use App\Models\Company;
use App\Models\Project;

class EnsureCanSendRequestAction extends Action
{
    public function execute(RequestDTO $requestDTO)
    {
        if (
            $requestDTO->for::class === User::class ||
            IsUserTypeAction::make()->execute($requestDTO->type)
        ) {
            return CanSendRequestForUserAction::make()->execute($requestDTO); 
        }

        if ($requestDTO->for::class === Company::class) {
            return CanSendRequestForCompanyAction::make()->execute($requestDTO);
        }
        
        if ($requestDTO->for::class === Project::class) {
            return CanSendRequestForProjectAction::make()->execute($requestDTO); 
        }
        
        throw new RequestException("Sorry, these set of data for this request does not meet any requirement.");
    }
}