<?php

namespace App\Actions\Relation;

use App\Actions\Action;
use App\DTOs\RelationDTO;
use App\Models\Relation;

class CreateRelationshipAction extends Action
{
    public function execute(RelationDTO $relationDTO) : Relation
    {
        $relation = $relationDTO->by->addedByRelations()->create([
            "relationship_type" => $relationDTO->relationshipType
        ]);
        $relation->to()->associate($relationDTO->to);
        $relation->save();

        return $relation;
    }
}