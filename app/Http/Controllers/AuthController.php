<?php

namespace App\Http\Controllers;

use App\DTOs\AuthLoginDTO;
use App\DTOs\AuthRegisterDTO;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = AuthService::login(
            AuthLoginDTO::fromRequest($request)
        );
    }
    
    public function register(RegisterRequest $request)
    {
        $user = AuthService::register(
            AuthRegisterDTO::fromRequest($request)
        );
    }
}
