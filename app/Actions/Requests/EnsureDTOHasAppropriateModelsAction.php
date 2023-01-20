<?php 

namespace App\Actions\Requests;

use App\Actions\Action;
use App\DTOs\RequestDTO;
use App\Exceptions\RequestException;

class EnsureDTOHasAppropriateModelsAction extends Action
{
    public function execute(RequestDTO $requestDTO)
    {
        if ($requestDTO->to && $requestDTO->from && $requestDTO->for) {
            return;
        }

        throw new RequestException("Sorry, to make a request you need the request from someone to another person, and regarding something.");
    }
}