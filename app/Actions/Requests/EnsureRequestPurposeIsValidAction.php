<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\DTOs\RequestDTO;
use App\Enums\ProjectParticipantEnum;
use App\Enums\RelationshipTypeEnum;
use App\Exceptions\RequestException;
use App\Models\Company;
use App\Models\Project;

class EnsureRequestPurposeIsValidAction extends Action
{
    public function execute(RequestDTO $requestDTO)
    {
        if (is_null($requestDTO->purpose)) {
            throw new RequestException("Sorry, the purpose of the request is required.");
        }
        
        if (
            isProjectPurpose::make()->execute($requestDTO->purpose) ||
            $this->isCompanyPurpose($requestDTO->purpose)
        ) {
            return; 
        }

        $for = $this->transformRequestForToString($requestDTO->for::class);

        throw new RequestException(
            "Sorry, {$requestDTO->purpose} is not a valid purpose for {$for}."
        );
    }

    private function transformRequestForToString(string $forClass)
    {
        return match($forClass) {
            Project::class => "projects",
            Company::class => "companies",
            'default' => null
        };
    }

    private function isCompanyPurpose(string $purpose): bool
    {
        return in_array(strtolower($purpose), RelationshipTypeEnum::COMPANYRELATIONSHIPALIASES);
    }
}