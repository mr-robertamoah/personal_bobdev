<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\Actions\Users\IsUserTypeAction;
use App\DTOs\RequestDTO;
use App\Enums\RelationshipTypeEnum;
use App\Exceptions\RequestException;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;

class EnsureRequestTypeIsValidAction extends Action
{
    public function execute(RequestDTO $requestDTO)
    {
        if (is_null($requestDTO->type)) {
            throw new RequestException("Sorry, the type of the request is required.");
        }
        
        if (
            IsProjectType::make()->execute($requestDTO->type) ||
            $this->isCompanyType($requestDTO->type) ||
            IsUserTypeAction::make()->execute($requestDTO->type)
        ) return; 

        $for = $this->transformRequestForToString(
            $requestDTO->for ? $requestDTO->for::class : null
        );

        throw new RequestException(
            "Sorry, {$requestDTO->type} is not a valid type for {$for}."
        );
    }

    private function transformRequestForToString(?string $forClass)
    {
        return match($forClass) {
            Project::class => "projects",
            Company::class => "companies",
            User::class => "user relationships",
            default => "relationships"
        };
    }

    private function isCompanyType(string $type): bool
    {
        return in_array(strtolower($type), RelationshipTypeEnum::COMPANYRELATIONSHIPALIASES);
    }
}