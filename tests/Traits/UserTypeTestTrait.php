<?php

namespace Tests\Traits;

use App\Models\UserType;
use Illuminate\Database\Eloquent\Factories\Sequence;

trait UserTypeTestTrait {
    
    private function createUserTypes()
    {
        $array = [
            ['name' => UserType::ADMIN, 'user_id' => 1],
            ['name' => UserType::SUPERADMIN, 'user_id' => 1],
            ['name' => UserType::STUDENT, 'user_id' => 1],
            ['name' => UserType::FACILITATOR, 'user_id' => 1],
            ['name' => UserType::DONOR, 'user_id' => 1],
        ];

        foreach ($array as $key => $value) {
            UserType::create($value);
        }
    }    

    private function createUserTypesUsingFactory()
    {
        UserType::factory()
            ->count(6)
            ->state(new Sequence(
                ['name' => UserType::ADMIN],
                ['name' => UserType::SUPERADMIN],
                ['name' => UserType::STUDENT],
                ['name' => UserType::FACILITATOR],
                ['name' => UserType::DONOR],
            ))
            ->create(['user_id' => 1]);
    }
}