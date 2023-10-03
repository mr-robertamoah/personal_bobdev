<?php

namespace App\Http\Controllers;

use App\DTOs\PermissionDTO;
use App\Http\Requests\CreatePermissionRequest;
use App\Http\Requests\DeletePermissionRequest;
use App\Http\Requests\SyncPermissionsAndRoleRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function createPermission(CreatePermissionRequest $request)
    {
        try {
            $permission = PermissionService::make()->createPermission(
                PermissionDTO::new()->fromArray([
                    "name" => $request->name,
                    "description" => $request->description,
                    "class" => $request->class,
                    "public" => $request->public,
                    "user" => $request->user()
                ])
            );
    
            return response()->json([
                "status" => true,
                "permission" => new PermissionResource($permission)
            ], 201);
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                "message" => $th->getMessage(),
            ], $th->getCode());
        }
    }

    public function updatePermission(UpdatePermissionRequest $request)
    {
        try {
            $permission = PermissionService::make()->updatePermission(
                PermissionDTO::new()->fromArray([
                    "name" => $request->name,
                    "description" => $request->description,
                    "class" => $request->class,
                    "public" => $request->public,
                    "user" => $request->user(),
                    "permissionId" => $request->permission_id
                ])
            );
    
            return response()->json([
                "status" => true,
                "permission" => new PermissionResource($permission)
            ], 201);
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                "message" => $th->getMessage(),
            ], $th->getCode());
        }
    }

    public function deletePermission(DeletePermissionRequest $request)
    {
        try {
            $status = PermissionService::make()->deletePermission(
                PermissionDTO::new()->fromArray([
                    "permissionId" => $request->permission_id,
                    "user" => $request->user()
                ])
            );

            return response()->json([
                "status" => $status,
            ]);
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                "message" => $th->getMessage(),
            ], $th->getCode());
        }
    }
    
    public function getPermissions(Request $request)
    {
        try {
            $permissions = PermissionService::make()->getPermissions(
                PermissionDTO::new()->fromArray([
                    "user" => $request->user(),
                    "name" => $request->name,
                    "like" => $request->like,
                    "class" => $request->class,
                ])
            );
            
            return PermissionResource::collection($permissions);
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                "message" => $th->getMessage(),
            ], $th->getCode());
        }
    }
}
