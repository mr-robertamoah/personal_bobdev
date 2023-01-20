<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\CompanyDTO;
use App\Enums\RelationshipTypeEnum;
use App\Exceptions\CompanyException;
use App\Models\User;

class EnsureIsRightCompanyRelationshipAction extends Action
{
    public function execute(string $relationshipType, User $user)
    {
        if (
            is_null($relationshipType) ||
            (is_string($relationshipType) && strlen($relationshipType) < 1)
        ) {
            throw new CompanyException("Sorry! the relationship type you wish to establish must be specified for user with name {$user->name}.");
        }
        
        if (RelationshipTypeEnum::isValidCompanyRelationship($relationshipType)) {
            return;
        }

        throw new CompanyException("Sorry! {$relationshipType} specified with user, with name {$user->name}, is not a valid relationship to associate with a company.");
    }
}