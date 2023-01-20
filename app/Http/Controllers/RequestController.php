<?php

namespace App\Http\Controllers;

use App\DTOs\RequestDTO;
use App\DTOs\ResponseDTO;
use App\Http\Resources\RequestResource;
use App\Services\RequestService;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'purpose' => 'required|string'
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
    }

    public function update(Request $request)
    {
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
    }

    private function getDTOInitializationData(Request $request): array
    {
        $dtoInitializationData = [
            'from' => $request->user(),
            'toId' => $request->toId,
            'toType' => $request->toType,
            'forId' => $request->forId,
            'forType' => $request->forType,
            'purpose' => $request->purpose
        ];

        if ($request->has('fromId') && $request->has('fromType')) {
            $dtoInitializationData = array_merge($dtoInitializationData, [
                'from' => null,
                'fromId' => $request->fromId,
                'fromType' => $request->fromType,
            ]);
        }

        return $dtoInitializationData;
    }
}
