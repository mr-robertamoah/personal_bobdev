<?php

namespace App\Http\Controllers;

use App\DTOs\ProfileDTO;
use App\Http\Resources\CompanyProfileResource;
use App\Http\Resources\UserProfileResource;
use App\Services\ProfileService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function getUserProfile(Request $request)
    {
        $profile = (new ProfileService)->getProfile(
            ProfileDTO::new()->fromArray([
                'profileableId' => $request->id,
                'profileableType' => $request->type,
            ])
        );

        return response()->json([
            "status" => true,
            "profile" => $request->type == 'user' ? 
                new UserProfileResource($profile) :
                new CompanyProfileResource($profile)
        ]);
    }
}
