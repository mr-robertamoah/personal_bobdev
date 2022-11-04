<?php

namespace App\Services;

use App\Actions\Requests\CheckHasAppropriateModelsAction;
use App\Actions\Requests\CheckIfCanRespondToRequestAction;
use App\Actions\Requests\CheckIfCanSendRequestAction;
use App\DTOs\RequestDTO;
use App\DTOs\ResponseDTO;
use App\Enums\RequestStateEnum;
use App\Events\RequestSentEvent;
use App\Models\Company;
use App\Models\Project;
use App\Models\Request;

class RequestService
{
    public function createRequest(RequestDTO $requestDTO)
    {
        $requestDTO = $this->setUpRequestModelsOnDTO($requestDTO);

        CheckHasAppropriateModelsAction::make()->execute($requestDTO);

        CheckIfCanSendRequestAction::make()->execute($requestDTO);

        $request = new Request([
            'state' => RequestStateEnum::pending->value,
            'purpose' => strtoupper($requestDTO->purpose),
        ]);

        $request->from()->associate($requestDTO->from);
        $request->to()->associate($requestDTO->to);
        $request->for()->associate($requestDTO->for);

        $request->save();

        // RequestSentEvent::broadcast($request);

        return $request->refresh();
    }

    public function respondToRequest(ResponseDTO $responseDTO)
    {
        $responseDTO = $responseDTO->withRequest(
            $responseDTO->request ?? Request::find($responseDTO->requestId)
        );

        CheckIfCanRespondToRequestAction::make()->execute($responseDTO);

        CheckIfResponseIsValidAction::make()->execute($responseDTO);
        
        $responseDTO->request->update([
            'state' => strtoupper($responseDTO->response)
        ])
    }

    private function setUpRequestModelsOnDTO(RequestDTO $requestDTO): RequestDTO
    {   
        if ($requestDTO->fromId && $requestDTO->fromType && 
            class_exists($class = $this->getModelClass($requestDTO->fromType))
        ) {
            $requestDTO = $requestDTO->withFrom($class::find($requestDTO->fromId));
        }
        
        if ($requestDTO->forId && $requestDTO->forType && 
            class_exists($class = $this->getModelClass($requestDTO->forType))
        ) {
            $requestDTO = $requestDTO->withFor($class::find($requestDTO->forId));
        }

        $class = $requestDTO->for::class;

        if ($requestDTO->for && ($class == Project::class || $class == Company::class)) {
            $requestDTO = $requestDTO->withTo($requestDTO->for->addedby);

            return $requestDTO;
        }

        if ($requestDTO->toId && $requestDTO->toType && 
            class_exists($class = $this->getModelClass($requestDTO->toType))
        ) {
            $requestDTO = $requestDTO->withTo($class::find($requestDTO->toId));
        }

        return $requestDTO;
    }

    private function getModelClass(string $modelName): string
    {
        return "App\\Models\\" . ucfirst(strtolower($modelName));
    }
}