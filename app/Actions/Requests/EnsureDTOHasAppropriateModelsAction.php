<?php 

namespace App\Actions\Requests;

use App\Actions\Action;
use App\Actions\Users\IsUserTypeAction;
use App\DTOs\RequestDTO;
use App\Exceptions\RequestException;

class EnsureDTOHasAppropriateModelsAction extends Action
{
    public function execute(RequestDTO $requestDTO)
    {
        if (
            $requestDTO->to && 
            ($requestDTO->from || (
                is_null($requestDTO->from) && 
                IsUserTypeAction::make()->execute($requestDTO->type)
            )) && 
            $requestDTO->for
        ) return;

        throw new RequestException("Sorry, to make a request you need the request from someone to another person, and regarding something.");
    }
}