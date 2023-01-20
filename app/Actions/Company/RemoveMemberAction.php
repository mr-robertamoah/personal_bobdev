<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\Enums\RelationshipTypeEnum;
use App\Models\Company;
use App\Models\User;

class RemoveMemberAction extends Action
{
    public function execute(Company $company, User $user)
    {
        $membership = $company->getRelationship($user);

        $membership->delete();
        
        return $membership->refresh();
    }
}