<?php

namespace App\Http\Controllers;

use App\Actions\ApiErrorHandlingAction;
use App\DTOs\RequestDTO;
use App\DTOs\ResponseDTO;
use App\Http\Resources\RequestResource;
use App\Services\RequestService;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function create(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|string',
                'purpose' => 'nullable|string',
            ]);
    
            $sentRequest = (new RequestService)->createRequest(
                RequestDTO::new()->fromArray(
                    $this->getDTOInitializationData($request)
                )
            );
    
            return response()->json([
                'status' => true,
                'request' => new RequestResource($sentRequest)
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return ApiErrorHandlingAction::make()
                ->execute($th);
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'response' => 'required|string'
            ]);
    
            $respondedRequest = (new RequestService)->respondToRequest(
                ResponseDTO::new()->fromArray([
                    'response' => $request->response,
                    'requestId' => $request->request_id,
                    'user' => $request->user(),
                ])
            );
    
            return response()->json([
                'status' => true,
                'request' => new RequestResource($respondedRequest)
            ]);
        } catch (\Throwable $th) {
            // throw $th;
            return ApiErrorHandlingAction::make()
                ->execute($th);            
        }
    }

    private function getDTOInitializationData(Request $request): array
    {
        $dtoInitializationData = [
            'user' => $request->user(),
            'from' => $request->user(),
            'toId' => $request->toId ?: $request->to_id,
            'toType' => $request->toType ?: $request->to_type,
            'forId' => $request->forId ?: $request->for_id,
            'forType' => $request->forType ?: $request->for_type,
            'purpose' => $request->purpose,
            'type' => $request->type,
        ];

        if ($request->has('fromId') && $request->has('fromType')) {
            $dtoInitializationData = array_merge($dtoInitializationData, [
                'from' => null,
                'fromId' => $request->fromId ?: $request->from_id,
                'fromType' => $request->fromType ?: $request->from_type,
            ]);
        }

        return $dtoInitializationData;
    }
}
