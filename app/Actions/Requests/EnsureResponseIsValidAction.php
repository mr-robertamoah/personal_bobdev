<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\DTOs\ResponseDTO;
use App\Enums\RequestStateEnum;
use App\Exceptions\ResponseException;

class EnsureResponseIsValidAction extends Action
{
    public function execute(ResponseDTO $responseDTO)
    {
        if (is_null($responseDTO->response)) {
            throw new ResponseException("Sorry! A response is required to respond to a request.");
        }

        if (in_array(strtoupper($responseDTO->response), RequestStateEnum::possibleResponse())) {
            return;
        }

        throw new ResponseException("Sorry! {$responseDTO->response} is not a valid response for a request.");
    }
}