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

            $this->loginUser($user, $request);
        
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
        try {
            
            $user = (new AuthService)->register(
                AuthRegisterDTO::fromRequest($request)
            );

            $this->loginUser($user, $request);
    
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
            $user = (new AuthService)->getAUser($request->username);

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

    private function loginUser($user, $request)
    {
        Auth::login($user);
    
        $request->session()->regenerate();
    }
}
