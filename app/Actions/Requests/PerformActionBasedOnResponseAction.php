<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\DTOs\ResponseDTO;
use App\Enums\RequestStateEnum;

class PerformActionBasedOnResponseAction extends Action
{
    public function execute(ResponseDTO $responseDTO)
    {
        if (strtoupper($responseDTO->response) != RequestStateEnum::accepted->value) {
            return;
        }

        if ($this->isAboutUser($responseDTO)) {
            return UserResponseAction::make()->execute($responseDTO);
        }

        if ($this->isAboutProject($responseDTO)) {
            return ProjectResponseAction::make()->execute($responseDTO);
        }
        
        if ($this->isAboutCompany($responseDTO)) {
            CompanyResponseAction::make()->execute($responseDTO);
        }
    }

    private function isAboutUser(ResponseDTO $responseDTO): bool
    {
        return $responseDTO->request->isForUser();
    }

    private function isAboutProject(ResponseDTO $responseDTO): bool
    {
        return $responseDTO->request->isForProject();
    }

    private function isAboutCompany(ResponseDTO $responseDTO): bool
    {
        return $responseDTO->request->isForCompany();
    }
}