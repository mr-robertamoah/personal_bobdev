<?php

namespace App\Http\Controllers;

use App\DTOs\AuthLoginDTO;
use App\DTOs\AuthRegisterDTO;
use App\Exceptions\AuthException;
use App\Exceptions\UserNotFoundException;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        try {
            $user = (new AuthService)->login(
                AuthLoginDTO::fromRequest($request)
            );

            $request->session()->regenerate();
        
            return response()->json([
                'status' => true,
                'user' => new UserResource($user)
            ]);
        }  catch (AuthException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Throwable $th) {
            throw $th;
            return response()->json([
                'status' => false,
                'message' => "Sorry, something happened while trying to login. Please try again later."
            ], 500);
        }
    }
    
    public function register(RegisterRequest $request)
    {
        //TODO work on the password strenght
        try {
            
            $user = (new AuthService)->register(
                AuthRegisterDTO::fromRequest($request)
            );

            $request->session()->regenerate();
    
            return response()->json([
                'status' => true,
                'user' => new UserResource($user)
            ]);
        } catch (\Throwable $th) {
            throw $th;
            return response()->json([
                'status' => false,
                'message' => "Sorry, something happened while trying to register. Please try again later."
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::guard('web')->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return response()->json([
                'status' => true
            ]);
        
        } catch (\Throwable $th) {
            throw $th;
            return response()->json([
                'status' => false,
                'message' => "Sorry, something happened while trying to logout. Please try again later."
            ], 500);
        }
    }
}
