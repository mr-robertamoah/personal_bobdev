<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\DTOs\ResponseDTO;
use App\Exceptions\ResponseException;

class EnsureRequestExistsAction extends Action
{
    public function execute(ResponseDTO $responseDTO)
    {
        if ($responseDTO->request) {
            return;
        }

        throw new ResponseException("Sorry! You need a request to respond to. No request was found.");
    }
}