<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\DTOs\RequestDTO;
use App\Enums\RelationshipTypeEnum;
use App\Exceptions\RequestException;
use App\Models\Company;
use App\Models\Project;

class EnsureRequestTypeIsValidAction extends Action
{
    public function execute(RequestDTO $requestDTO)
    {
        if (is_null($requestDTO->type)) {
            throw new RequestException("Sorry, the type of the request is required.");
        }
        
        if (
            IsProjectType::make()->execute($requestDTO->type) ||
            $this->isCompanyType($requestDTO->type)
        ) {
            return; 
        }

        $for = $this->transformRequestForToString($requestDTO->for::class);

        throw new RequestException(
            "Sorry, {$requestDTO->type} is not a valid type for {$for}."
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

    private function isCompanyType(string $type): bool
    {
        return in_array(strtolower($type), RelationshipTypeEnum::COMPANYRELATIONSHIPALIASES);
    }
}