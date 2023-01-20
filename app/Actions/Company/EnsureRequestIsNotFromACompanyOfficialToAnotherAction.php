<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\RequestDTO;
use App\Exceptions\CompanyException;

class EnsureRequestIsNotFromACompanyOfficialToAnotherAction extends Action
{
    public function execute(RequestDTO $requestDTO)
    {
        if (
            $this->isNotFromOfficial($requestDTO) ||
            $this->isNotToOfficial($requestDTO)
        ) {
            return;
        }

        throw new CompanyException("Sorry! Both sender and recepient cannot be officials of the company with name {$requestDTO->for->name} company.");
    }

    private function isNotFromOfficial(RequestDTO $requestDTO)
    {
        return !$requestDTO->for->isOfficial($requestDTO->from);
    }

    private function isNotToOfficial(RequestDTO $requestDTO)
    {
        return !$requestDTO->for->isOfficial($requestDTO->to);
    }
}