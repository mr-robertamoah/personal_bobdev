<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function createUser()
    {
        return User::create([
            'username' => "mr_robert",
            'first_name' => "Robert",
            'surname' => "Robert",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);
    }
    
    public function testCannotGetProfileWithoutTypeParameterOfApiBeingUserOrCompany()
    {
        $user = $this->createUser();

        $this->actingAs($user);

        $response = $this->getJson("/api/profile/user/{$user->id}");
        ds($response);
        $response->assertStatus(200);
    }
    // test if wards are loaded
    // test if parents are loaded
    // test if company members are loaded
}
