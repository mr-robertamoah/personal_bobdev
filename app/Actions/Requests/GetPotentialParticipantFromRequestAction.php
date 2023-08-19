<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\Actions\IsUserAnOfficialOfModelAction;
use App\Models\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GetPotentialParticipantFromRequestAction extends Action
{
    public function execute(Request $request): Model
    {
        if (
            ($request->to::class != User::class && $request->from::class == User::class) ||
            IsUserAnOfficialOfModelAction::make()->execute($request->to, $request->for) ||
            IsToFacilitatorAndHasALearnerPurpose::make()->execute($request)
        ) {
            return $request->from;
        }

        return $request->to;
    }
}