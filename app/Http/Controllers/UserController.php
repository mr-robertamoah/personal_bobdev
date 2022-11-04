<?php

namespace App\Http\Controllers;

use App\DTOs\UserDTO;
use App\Exceptions\UserNotFoundException;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUser(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => (bool) $user,
            'user' => $user ? new UserResource($user) : null
        ]);
    }

    public function getAUser(Request $request)
    {
        try {
            $user = (new UserService)->getAUser($request->username);

            return response()->json([
                'status' => (bool) $user,
                'user' => $user ? new UserResource($user) : null
            ]);  
        } catch (UserNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Throwable $th) {
            throw $th;
            return response()->json([
                'status' => false,
                'message' => "Sorry, something happened while trying to get user with username: {$request->username}. Please try again later."
            ], 500);
        }
    }
    
    public function editInfo(Request $request)
    {
        $user = (new UserService)->editInfo(
            UserDTO::new()->fromArray([
                'firstName' => $request->get('firstName'),
                'surname' => $request->get('surname'),
                'otherNames' => $request->get('otherNames'),
                'email' => $request->get('email'),
                'gender' => $request->get('gender'),
                'currentUser' => $request->user(),
                'userId' => $request->route('id'),
                'username' => $request->route('id'),
            ])
        );

        return response()->json([
            'status' => true,
            'user' => new UserResource($user)
        ]);
    }
    
    public function resetPassword(Request $request)
    {
        //TODO work on the password strenght
        $user = (new UserService)->resetPassword(
            UserDTO::new()->fromArray([
                'password' => $request->get('password'),
                'passwordConfirmation' => $request->get('passwordConfirmation'),
                'currentPassword' => $request->get('currentPassword'),
                'currentUser' => $request->user(),
                'userId' => $request->route('id'),
                'username' => $request->route('id'),
            ])
        );

        return response()->json([
            'status' => true,
            'user' => new UserResource($user)
        ]);
    }
}
