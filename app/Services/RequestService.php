<?php

namespace App\Services;

use App\Actions\Activity\AddActivityAction;
use App\Actions\GetModelInstanceFromIdAndClassNameIfModelIsNull;
use App\Actions\Requests\EnsureDTOHasAppropriateModelsAction;
use App\Actions\Requests\EnsureUserCanRespondToRequestAction;
use App\Actions\Requests\EnsureCanSendRequestAction;
use App\Actions\Requests\EnsureRequestExistsAction;
use App\Actions\Requests\EnsureResponseIsValidAction;
use App\Actions\Requests\CreateRequestAction;
use App\Actions\Requests\EnsureRequestTypeIsValidAction;
use App\Actions\Requests\PerformActionBasedOnResponseAction;
use App\DTOs\ActivityDTO;
use App\DTOs\RequestDTO;
use App\DTOs\ResponseDTO;
use App\Models\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RequestService
{
    public function createRequest(RequestDTO $requestDTO)
    {
        $requestDTO = $this->setUpRequestModelsOnDTO($requestDTO);

        EnsureDTOHasAppropriateModelsAction::make()->execute($requestDTO);
        
        EnsureRequestTypeIsValidAction::make()->execute($requestDTO);

        EnsureCanSendRequestAction::make()->execute($requestDTO);

        $request = CreateRequestAction::make()->execute($requestDTO);

        if (
            ($request->isNotForUser() && $request->for->isofficial($requestDTO->from)) ||
            $requestDTO->from->isAdmin()
        )
        {
            AddActivityAction::make()->execute(
                ActivityDTO::new()->fromArray([
                    'performedby' => $requestDTO->from::class != User::class ? $requestDTO->user : $requestDTO->from,
                    'performedon' => $request,
                    'action' => 'request',
                ])
            );
        }
        // RequestSentEvent::broadcast($request);

        return $request->refresh();
    }

    public function respondToRequest(ResponseDTO $responseDTO)
    {
        $responseDTO = $responseDTO->withRequest(
            $responseDTO->request ?? Request::find($responseDTO->requestId)
        );
        
        $responseDTO = $responseDTO->withUser(
            $responseDTO->user ?? User::find($responseDTO->userId)
        );

        EnsureRequestExistsAction::make()->execute($responseDTO);

        EnsureResponseIsValidAction::make()->execute($responseDTO);

        EnsureUserCanRespondToRequestAction::make()->execute($responseDTO);
        
        $response = strtoupper($responseDTO->response);

        $responseDTO->request->update([
            'state' => $response
        ]);

        PerformActionBasedOnResponseAction::make()->execute($responseDTO);
        
        if (
            ($responseDTO->request->isNotForUser() && $responseDTO->request->for->isofficial($responseDTO->user) )||
            $responseDTO->user->isAdmin()
        ) {
            AddActivityAction::make()->execute(
                ActivityDTO::new()->fromArray([
                    'performedby' => $responseDTO->user,
                    'performedon' => $responseDTO->request,
                    'action' => 'respond',
                    'data' => [
                        'response' => $response
                    ]
                ])
            );
        }
        
        return $responseDTO->request->refresh();
    }

    private function setUpRequestModelsOnDTO(RequestDTO $requestDTO): RequestDTO
    {
        $requestDTO = $requestDTO->withFrom(
            GetModelInstanceFromIdAndClassNameIfModelIsNull::make()->execute(
                $requestDTO->fromId,
                $requestDTO->fromType,
                $requestDTO->from
            )
        );

        $requestDTO = $requestDTO->withTo(
            GetModelInstanceFromIdAndClassNameIfModelIsNull::make()->execute(
                $requestDTO->toId,
                $requestDTO->toType,
                $requestDTO->to
            )
        );

        $requestDTO = $requestDTO->withFor(
            GetModelInstanceFromIdAndClassNameIfModelIsNull::make()->execute(
                $requestDTO->forId,
                $requestDTO->forType,
                $requestDTO->for
            )
        );

        if (is_null($requestDTO->for)) {
            $requestDTO = $requestDTO->withFor($requestDTO->to);
        }

        if ($requestDTO->to) {
            return $requestDTO;
        }

        $requestDTO = $requestDTO->withTo(
            $this->getOwnerOfForModel($requestDTO)
        );

        return $requestDTO;
    }

    private function getOwnerOfForModel(RequestDTO $requestDTO): Model|null
    {
        if (is_null($requestDTO->for)) {
            return null;
        }

        return $requestDTO->for->owner;
    }
}