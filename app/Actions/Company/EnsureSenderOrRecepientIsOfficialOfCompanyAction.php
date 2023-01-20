<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\RequestDTO;
use App\Exceptions\CompanyException;

class EnsureSenderOrRecepientIsOfficialOfCompanyAction extends Action
{
    public function execute(RequestDTO $requestDTO)
    {
        if (
            $this->isFromOfficial($requestDTO) ||
            $this->isToOfficial($requestDTO)
        ) {
            return;
        }

        throw new CompanyException("Sorry! The sender or recepient must be an official of the company with name {$requestDTO->for->name} company.");
    }

    private function isFromOfficial(RequestDTO $requestDTO)
    {
        return $requestDTO->for->isOfficial($requestDTO->from) || $requestDTO->from->isAdmin();
    }

    private function isToOfficial(RequestDTO $requestDTO)
    {
        return $requestDTO->for->isOfficial($requestDTO->to) || $requestDTO->to->isAdmin();
    }
}