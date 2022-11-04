<?php

namespace Tests\Feature;

use App\Exceptions\UserException;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotEditInfoIfNotAdminAndUser()
    {
        // $this->expectException(UserException::class);
        // $this->expectExceptionMessage("Sorry 😏, you are not authorized to perform this action.");

        $currentUser = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $this->actingAs($currentUser);

        $response = $this->postJson("/api/user/{$user->id}/edit-info",[

        ]);

        $response->assertStatus(500);
    }

    public function testCannotEditInfoIfUserNotFound()
    {
        // $this->expectException(UserException::class);
        // $this->expectExceptionMessage("Sorry 😏, user was not found.");

        $currentUser = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $this->actingAs($currentUser);

        $response = $this->postJson("/api/user/10/edit-info",[

        ]);

        $response->assertStatus(500);
    }

    public function testCannotEditInfoWithoutEnoughDataUser()
    {
        $currentUser = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $userType = $currentUser->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $currentUser->userTypes()->attach($userType->id);

        $this->actingAs($currentUser);

        $response = $this->postJson("/api/user/{$currentUser->id}/edit-info",[
        ]);

        $response
            ->assertStatus(500);

        $this->assertDatabaseHas('users', [
            'first_name' => 'Robert'
        ]);
    }

    public function testCanEditInfoAsTheUser()
    {
        $currentUser = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $this->actingAs($currentUser);

        $response = $this->postJson("/api/user/{$currentUser->id}/edit-info",[
            'firstName' => 'Roberto'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'user' => [
                    'name' => 'Amoah Roberto'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'Roberto'
        ]);
    }

    public function testCanEditInfoOfUserAsAdmin()
    {
        $currentUser = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $userType = $currentUser->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $currentUser->userTypes()->attach($userType->id);
        
        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $this->actingAs($currentUser);

        $response = $this->postJson("/api/user/{$user->id}/edit-info",[
            'firstName' => 'Roberto'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'user' => [
                    'name' => 'Amoah Roberto'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'Roberto',
            'username' => 'mr_robertamoah1'
        ]);
    }

    public function testCannotResetPasswordIfNotAdminAndUser()
    {
        // $this->expectException(UserException::class);
        // $this->expectExceptionMessage("Sorry 😏, you are not authorized to perform this action.");

        $curentUser = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $this->actingAs($curentUser);

        $response = $this->postJson("/api/user/{$user->id}/reset-password",[

        ]);

        $response->assertStatus(500);
    }

    public function testCannotResetPasswordIfPasswordIsNotGiven()
    {
        // $this->expectException(UserException::class);
        // $this->expectExceptionMessage("Sorry 😏, you are not authorized to perform this action.");

        $curentUser = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/user/{$user->id}/reset-password",[

        ]);

        $response->assertStatus(500);
    }

    public function testCannotResetPasswordIfPasswordAndConfirmationPasswordsDoNotMatch()
    {
        // $this->expectException(UserException::class);
        // $this->expectExceptionMessage("Sorry 😏, you are not authorized to perform this action.");

        $curentUser = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/user/{$user->id}/reset-password",[
            'currentPassword' => 'password',
            'password' => 'password123',
            'passwordConfirmation' => 'password1234',
        ]);

        $response->assertStatus(500);
    }

    public function testCannotResetPasswordIfCurrentPasswordIsWrongAndNotAdmin()
    {
        // $this->expectException(UserException::class);
        // $this->expectExceptionMessage("Sorry 😏, you are not authorized to perform this action.");

        $curentUser = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/user/{$user->id}/reset-password",[
            'currentPassword' => 'password1',
            'password' => 'password123',
            'passwordConfirmation' => 'password123',
        ]);

        $response->assertStatus(500);
    }

    public function testCanResetPasswordOfUserIfCurrentPasswordIsWrongAndAdmin()
    {
        $currentUser = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $userType = $currentUser->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $currentUser->userTypes()->attach($userType->id);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $this->actingAs($currentUser);

        $response = $this->postJson("/api/user/{$user->id}/reset-password",[
            'currentPassword' => 'password1',
            'password' => 'password123',
            'passwordConfirmation' => 'password123',
        ]);

        $response->assertStatus(200);
        
        $this->assertTrue(Hash::check('password123', $user->refresh()->password));
    }

    public function testCanResetPasswordOfOwnAccountUsingId()
    {
        $currentUser = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $this->actingAs($currentUser);

        $response = $this->postJson("/api/user/{$currentUser->id}/reset-password",[
            'currentPassword' => 'password',
            'password' => 'password123',
            'passwordConfirmation' => 'password123',
        ]);

        $response->assertStatus(200);
        
        $this->assertTrue(Hash::check('password123', $currentUser->refresh()->password));
    }

    public function testCanResetPasswordOfOwnAccountUsingUsername()
    {
        $currentUser = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $this->actingAs($currentUser);

        $response = $this->postJson("/api/user/{$currentUser->username}/reset-password",[
            'currentPassword' => 'password',
            'password' => 'password123',
            'passwordConfirmation' => 'password123',
        ]);

        $response->assertStatus(200);
        
        $this->assertTrue(Hash::check('password123', $currentUser->refresh()->password));
    }
}
