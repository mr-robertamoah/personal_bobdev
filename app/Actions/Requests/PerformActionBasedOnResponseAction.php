<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\DTOs\ResponseDTO;
use App\Enums\RequestStateEnum;
use App\Models\Company;
use App\Models\Project;

class PerformActionBasedOnResponseAction extends Action
{
    public function execute(ResponseDTO $responseDTO)
    {
        if (strtoupper($responseDTO->response) != RequestStateEnum::accepted->value) {
            return;
        }

        if ($this->isAboutProject($responseDTO)) {
            return ProjectResponseAction::make()->execute($responseDTO);
        }
        
        if ($this->isAboutCompany($responseDTO)) {
            CompanyResponseAction::make()->execute($responseDTO);
        }
    }

    private function isAboutProject(ResponseDTO $responseDTO): bool
    {
        return $responseDTO->request->for::class === Project::class;
    }

    private function isAboutCompany(ResponseDTO $responseDTO): bool
    {
        return $responseDTO->request->for::class === Company::class;
    }
}