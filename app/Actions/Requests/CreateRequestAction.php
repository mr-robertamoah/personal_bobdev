<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\DTOs\RequestDTO;
use App\Enums\RequestStateEnum;
use App\Models\Request;

class CreateRequestAction extends Action
{
    public function execute(RequestDTO $requestDTO): Request
    {
        $request = new Request([
            'state' => RequestStateEnum::pending->value,
            'type' => strtoupper(
                strtolower($requestDTO->type) == "learner" ? "student" : $requestDTO->type
            ),
            'purpose' => $requestDTO->purpose,
        ]);

        $request->from()->associate($requestDTO->from);
        $request->to()->associate($requestDTO->to);
        $request->for()->associate($requestDTO->for);

        $request->save();

        return $request;
    }
}