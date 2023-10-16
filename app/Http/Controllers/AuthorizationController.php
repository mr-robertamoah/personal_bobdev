<?php

namespace App\Http\Controllers;

use App\DTOs\AuthorizationDTO;
use App\Http\Requests\CreateAuthorizationRequest;
use App\Http\Resources\AuthorizationResource;
use App\Services\AuthorizationService;
use Illuminate\Http\Request;

class AuthorizationController extends Controller
{
    public function createAuthorization(CreateAuthorizationRequest $request)
    {
        try {
            $authorization = AuthorizationService::make()->attachAuthorizationsAndUsers(
                AuthorizationDTO::new()->fromArray([
                    "authorizableType" => $request->authorizable_type,
                    "authorizableId" => $request->authorizable_id,
                    "authorizationType" => $request->authorization_type,
                    "authorizationId" => $request->authorization_id,
                    "authorizedId" => $request->authorized_id,
                    "user" => $request->user()
                ])
            );
    
            return response()->json([
                "status" => true,
                "authorization" => new AuthorizationResource($authorization)
            ], 201);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                "message" => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }
    public function deleteAuthorization(Request $request)
    {
        try {
            $status = AuthorizationService::make()->detachAuthorizationsAndUsers(
                AuthorizationDTO::new()->fromArray([
                    "mainAuthorizationId" => $request->authorization_id,
                    "user" => $request->user()
                ])
            );

            return response()->json([
                "status" => $status,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                "message" => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }
    
    public function getAuthorizations(Request $request)
    {
        try {
            $authorizations = AuthorizationService::make()->getAuthorizations(
                AuthorizationDTO::new()->fromArray([
                    "authorizableType" => $request->authorizable_type,
                    "authorizableId" => $request->authorizable_id,
                    "authorizationType" => $request->authorization_type,
                    "authorizationId" => $request->authorization_id,
                    "authorizedId" => $request->authorizedId,
                    "user" => $request->user(),
                    "name" => $request->name,
                    "page" => $request->page,
                ])
            );
            
            return AuthorizationResource::collection($authorizations);
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                "message" => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }
}
