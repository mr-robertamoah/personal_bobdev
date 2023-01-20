<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\Actions\Project\IsLearnerParticipantTypeAction;
use App\Models\Project;
use App\Models\Request;

class IsToFacilitatorAndHasALearnerPurpose extends Action
{
    public function execute(Request $request): bool
    {
        if ($request->for::class != Project::class) {
            return false;
        }
        
        return $request->for->isFacilitator($request->to) && 
            IsLearnerParticipantTypeAction::make()->execute($request->purpose);
    }
}