<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\Enums\RelationshipTypeEnum;
use App\Exceptions\CompanyException;
use App\Models\User;

class EnsureUserIsAnAdultIfAdministratorRelationshipTypeAction extends Action
{
    public function execute(User $user, string $relationshipType)
    {
        if (
            $user->isAdult() || 
            in_array(strtolower($relationshipType), RelationshipTypeEnum::COMPANYMEMBERALIASES)
        ) {
            return;
        }

        throw new CompanyException("Sorry! {$user->name} must be an adult in order to have such a relationship with a company.");
    }
}