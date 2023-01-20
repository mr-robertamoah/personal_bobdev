<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\Enums\RelationshipTypeEnum;
use App\Models\Company;
use App\Models\User;

class BecomeCompanyMemberAction extends Action
{
    public function execute(Company $company, User $user, string $type)
    {
        $membership = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyRelationshipFromString($type)
        ]);

        $membership->to()->associate($user);

        $membership->save();
        
        return $membership;
    }
}