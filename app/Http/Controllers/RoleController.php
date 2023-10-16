<?php

namespace App\Http\Controllers;

use App\DTOs\RoleDTO;
use App\Http\Requests\CreatePermissionRequest;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\SyncPermissionsAndRoleRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function createRole(CreateRoleRequest $request)
    {
        try {
            $role = RoleService::make()->createRole(
                RoleDTO::new()->fromArray([
                    "name" => $request->name,
                    "description" => $request->description,
                    "class" => $request->class,
                    "public" => $request->public,
                    "user" => $request->user()
                ])
            );
    
            return response()->json([
                "status" => true,
                "role" => new RoleResource($role)
            ], 201);
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                "message" => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }

    public function updateRole(UpdateRoleRequest $request)
    {
        try {
            $role = RoleService::make()->updateRole(
                RoleDTO::new()->fromArray([
                    "name" => $request->name,
                    "description" => $request->description,
                    "class" => $request->class,
                    "public" => $request->public,
                    "user" => $request->user(),
                    "roleId" => $request->role_id
                ])
            );
    
            return response()->json([
                "status" => true,
                "role" => new RoleResource($role)
            ], 201);
        } catch (\Throwable $th) {
            throw $th;
            return response()->json([
                "message" => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }

    public function deleteRole(Request $request)
    {
        try {
            $status = RoleService::make()->deleteRole(
                RoleDTO::new()->fromArray([
                    "roleId" => $request->role_id,
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
            ], $th->getCode() ?: 500);
        }
    }
    
    public function getRoles(Request $request)
    {
        try {
            $roles = RoleService::make()->getRoles(
                RoleDTO::new()->fromArray([
                    "user" => $request->user(),
                    "name" => $request->name,
                    "like" => $request->like,
                    "class" => $request->class,
                    "page" => $request->page ?: null,
                    "permissionName" => $request->permission_name,
                ])
            );
            
            return RoleResource::collection($roles);
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                "message" => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }
    
    public function syncPermissionsAndRole(SyncPermissionsAndRoleRequest $request)
    {
        try {
            $role = RoleService::make()->syncPermissionsAndRole(
                RoleDTO::new()->fromArray([
                    "user" => $request->user(),
                    "roleId" => $request->role_id,
                    "permissionIds" => $request->permission_ids,
                ])
            );
            
            return response()->json([
                "status" => true,
                "role" => new RoleResource($role)
            ]);
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                "message" => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }
}
