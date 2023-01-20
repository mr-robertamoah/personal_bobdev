<?php

namespace App\Http\Controllers;

use App\DTOs\ProfileDTO;
use App\Http\Resources\UserProfileResource;
use App\Services\ProfileService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function getUserProfile(Request $request)
    {
        $user = (new ProfileService)->getUserProfile(
            ProfileDTO::new()->fromArray([
                'userId' => $request->id
            ])
        );

        return response()->json([
            "status" => true,
            "profile" => new UserProfileResource($user)
        ]);
    }
}
