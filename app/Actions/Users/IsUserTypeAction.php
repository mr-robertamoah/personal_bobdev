<?php

namespace App\Actions\Users;
use App\Actions\Action;
use App\Enums\RelationshipTypeEnum;

class IsUserTypeAction extends Action
{
    public function execute(string $type)
    {
        return in_array(strtoupper($type), RelationshipTypeEnum::userRelationships());
    }
}