<?php

namespace App\Services;

use App\DTOs\AuthLoginDTO;
use App\DTOs\AuthRegisterDTO;
use App\Events\UserRegisteredEvent;
use App\Exceptions\AuthException;
use App\Exceptions\UserNotFoundException;
use App\Models\User;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService extends Service
{
    public function login(AuthLoginDTO $authDTO)
    {
        if (Auth::check()) {
            throw new AuthException('Sorry! User is already logged in.');
        }
        
        $loginData = $authDTO->getData(all: true);

        $query = User::query();

        if ($loginData['email']) {
            $query->where('email', $loginData['email']);
        }

        if ($loginData['username']) {
            $query->where('username', $loginData['username']);
        }

        $user = $query->first();

        if (is_null($user)) {
            throw new AuthException('Sorry! There is no user with such credentials.');
        }

        if (!Hash::check($loginData['password'], $user->password)) {
            throw new AuthException('Sorry! The password given does not match that for the user with the given username or email');
        }

        Auth::login($user);

        return $user;
    }
    
    public function register(AuthRegisterDTO $authDTO)
    {
        $data = array_merge($authDTO->getData(all: true), [
            'password'=> Hash::make($authDTO->password),
            'gender' => $authDTO->gender ? strtoupper($authDTO->gender) : null
        ]);

        $user = User::create($data);

        if (is_null($user)) {
            throw new AuthException('Sorry! Creation of the user with the given credentials failed. Please try again later.');
        }

        UserRegisteredEvent::dispatch($user);

        Auth::login($user);

        return $user;
    }
}