<?php

namespace App\Http\Controllers;

use App\DTOs\UserTypeDTO;
use App\Exceptions\UserTypeException;
use App\Http\Requests\BecomeUserTypeRequest;
use App\Http\Resources\UserTypeResource;
use App\Services\UserTypeService;
use Illuminate\Http\Request;

class UserTypeController extends Controller
{
    public function become(BecomeUserTypeRequest $request)
    {
        $userType = (new UserTypeService)->becomeUserType(
            UserTypeDTO::fromArray([
                'userId' => $request->user()->id,
                'attachedUserId' => $request->userId,
                'name' => $request->userType
            ])
        );

        return response()->json([
            'status' => true,
            'userType' => new UserTypeResource($userType)
        ]);
    }

    
    public function remove(Request $request)
    {
        $userType = (new UserTypeService)->removeUserType(
            UserTypeDTO::fromArray([
                'userId' => $request->user()->id,
                'attachedUserId' => $request->userId,
                'name' => $request->userType
            ])
        );

        return response()->json([
            'status' => true
        ]);
    }
}
