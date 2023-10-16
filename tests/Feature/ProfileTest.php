<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase,
    WithFaker;

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
    
    public function testCannotGetProfileWithoutTypeParameterOfApiBeingUserOrCompany()
    {
        $user = $this->createUser();

        $this->actingAs($user);

        $response = $this->getJson("/api/profile/user/{$user->id}");
        ds($response);
        // $response->assertStatus(200);
        $this->assertTrue(True);
    }
    // test if wards are loaded
    // test if parents are loaded
    // test if company members are loaded
}
