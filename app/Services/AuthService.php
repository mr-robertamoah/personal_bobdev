<?php

namespace App\Services;

use App\DTOs\AuthLoginDTO;
use App\DTOs\AuthRegisterDTO;

class AuthService extends Service
{
    public function login(AuthLoginDTO $authDTO)
    {
        dd($authDTO);
    }
    
    public function register(AuthRegisterDTO $authDTO)
    {
        dd($authDTO);
    }
}