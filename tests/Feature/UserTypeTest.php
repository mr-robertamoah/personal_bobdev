<?php

namespace Tests\Feature;

use App\Enums\UserTypeEnum;
use App\Exceptions\UserException;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\UserTypeTestTrait;

class UserTypeTest extends TestCase
{
    use RefreshDatabase, UserTypeTestTrait;

    public function testCannotBecomeFacilitatorAsUserWhenNotAnAdult()
    {
        // $this->expectException(UserException::class);
        // $this->expectExceptionMessage("Sorry! User, with name Robert Amoah, has not yet specified date of birth.");

        $this->createUserTypes();

        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $this->actingAs($user);

        $facilitatorUserType = UserType::where('name', UserTypeEnum::facilitator->value)->first();
        
        $response = $this->postJson('/api/user-type/become', [
            'userId' => $user->id,
            'userType' => 'facilitator'
        ]);

        $response->assertStatus(500);
        
        $this->assertDatabaseMissing('user_user_type', [
            'user_id' => $user->id,
            'user_type_id' => $facilitatorUserType->id,
        ]);
    }

    public function testCanBecomeFacilitatorAsUser()
    {
        $this->createUserTypes();

        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYear(20)->toDateTimeString()
        ]);

        $this->actingAs($user);

        $facilitatorUserType = UserType::where('name', UserTypeEnum::facilitator->value)->first();
        
        $response = $this->postJson('/api/user-type/become', [
            'userId' => $user->id,
            'userType' => 'facilitator'
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('user_user_type', [
            'user_id' => $user->id,
            'user_type_id' => $facilitatorUserType->id,
        ]);
    }
}
