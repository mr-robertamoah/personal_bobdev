<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\Actions\Relation\CreateRelationshipAction;
use App\DTOs\RelationDTO;
use App\DTOs\ResponseDTO;
use App\Models\Relation;

class UserResponseAction extends Action
{
    public function execute(ResponseDTO $responseDTO) : Relation
    {
        $relationDTO = RelationDTO::new()->fromArray([
            'to' => $responseDTO->request->to,
            'by' => $responseDTO->request->from,
            'relationshipType' => $responseDTO->request->type
        ]);

        return CreateRelationshipAction::make()->execute($relationDTO);
    }
}