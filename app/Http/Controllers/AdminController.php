<?php

namespace App\Http\Controllers;

use App\Http\Resources\FullNormalUserResource;
use App\Http\Resources\FullUserResource;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function verify(Request $request)
    {
        $type = $request->user()->isSuperAdmin() == UserType::SUPERADMIN ? UserType::SUPERADMIN : UserType::ADMIN;

        if ($type === UserType::SUPERADMIN) {
            return response()->json([
                'isAdmin' => true
            ]);
        }

        if ($type === UserType::ADMIN) {
            return response()->json([
                'isAdmin' => true
            ]);
        }
        
        return response()->json([
            'isAdmin' => false
        ], 402);
    }

    public function getUsers(Request $request)
    {
        $name = $request->query('name');
        $type = $request->query('type') ? strtoupper($request->query('type')) : null;

        $query = User::query()
            ->with(['userTypes'])
            ->when($name, function($query) use ($name) {
                return $query->where('name', 'like', "%{$name}%");
            })
            ->when($type, function($query) use ($type) {
                return $query->whereUserType($type);
            });

        $isSuperAdmin = $request->user()->isSuperAdmin();

        $users = $query->paginate(5);
        
        return $isSuperAdmin ?
                FullUserResource::collection($users)->response()->getData(true) :
                FullNormalUserResource::collection($users)->response()->getData(true);
    }

    public function getGeneralInfo(Request $request)
    {
        $counts = [
            'users_count' => User::count(),
            'parents_count' => UserType::where('name', UserType::PARENT)->count(),
            'students_count' => UserType::where('name', UserType::STUDENT)->count(),
            'facilitators_count' => UserType::where('name', UserType::FACILITATOR)->count(),
            'donors_count' => UserType::where('name', UserType::DONOR)->count(),
            'admins_count' => UserType::where('name', UserType::ADMIN)->count(),
        ];

        if ($request->user()->isSuperAdmin()) {
            $counts['superadmins_count'] = UserType::where('name', UserType::SUPERADMIN)->count();
        }

        return response()->json($counts);
    }
}
