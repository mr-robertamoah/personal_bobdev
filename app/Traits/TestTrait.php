<?php 

namespace App\Traits;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;

trait TestTrait
{
    use WithFaker;
    
    private function createUser()
    {
        return User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);
    }
    
    private function createAdultUser()
    {
        return User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
            "dob" => Carbon::now()->subYears(User::ADULTAGE)
        ]);
    }
}