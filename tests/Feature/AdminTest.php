<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('users')->delete();
        DB::table('user_types')->delete();
    }
    
    public function testSuperAdminCanGetAllUsersAndAllUserTypes()
    {
        $user = User::create([
            'username' => "mr_robert",
            'first_name' => "Robert",
            'surname' => "Robert",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);
        
        $otherUser = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $superAdminUserType = $user->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);
        $donorUserType = $user->addedUserTypes()->create([
            'name' => UserType::DONOR
        ]);
        $user->userTypes()->attach($superAdminUserType->id);
        $otherUser->userTypes()->attach($donorUserType->id);

        $this->actingAs($user);

        $response = $this->get('/api/admin/users');

        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    [
                        "name" => "Robert Robert",
                        "username" => "mr_robert",
                        "email" => "mr_robertamoah@yahoo.com",
                        "gender" => "",
                        "userTypes" => [
                            [
                                "id" => 1,
                                'name' => 'SUPERADMIN',
                                'usableName' => 'super admin',
                                'description' => null
                            ]
                        ]
                    ],
                    [
                        "name" => "Amoah Robert",
                        "username" => "mr_robertamoah",
                        "email" => "mr_robertamoah1@yahoo.com",
                        "gender" => "",
                        "userTypes" => [
                            [
                                "id" => 2,
                                'name' => 'DONOR',
                                'usableName' => 'donor',
                                'description' => null
                            ]
                        ]
                    ]
                ]
            ]);
    }
    
    public function testAdminCanGetAllUsersAndAllUserTypesExceptSuperAdmin()
    {
        $superAdminUser = User::create([
            'username' => "paaabey",
            'first_name' => "Paa",
            'surname' => "Abey",
            'password' => bcrypt("password"),
            'email' => "paaabey@yahoo.com",
        ]);
        
        $otherUser = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah2@yahoo.com",
        ]);

        $superAdminUserType = $superAdminUser->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);
        $donorUserType = $superAdminUser->addedUserTypes()->create([
            'name' => UserType::DONOR
        ]);
        $superAdminUser->userTypes()->attach([$superAdminUserType->id, $donorUserType->id]);
        $otherUser->userTypes()->attach($donorUserType->id);

        $user = User::create([
            'username' => "mr_robert",
            'first_name' => "Robert",
            'surname' => "Robert",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);
        $user->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $response = $this->get('/api/admin/users');

        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    [
                        "name" => "Abey Paa",
                        "username" => "paaabey",
                        "email" => "paaabey@yahoo.com",
                        "gender" => "",
                        "userTypes" => [
                            [
                                "id" => 2,
                                'name' => 'DONOR',
                                'usableName' => 'donor',
                                'description' => null
                            ]
                        ]
                    ],
                    [
                        "name" => "Amoah Robert",
                        "username" => "mr_robertamoah",
                        "email" => "mr_robertamoah2@yahoo.com",
                        "gender" => "",
                        "userTypes" => [
                            [
                                "id" => 2,
                                'name' => 'DONOR',
                                'usableName' => 'donor',
                                'description' => null
                            ]
                        ]
                    ],
                    [
                        "name" => "Robert Robert",
                        "username" => "mr_robert",
                        "email" => "mr_robertamoah@yahoo.com",
                        "gender" => "",
                        "userTypes" => [
                            [
                                "id" => 3,
                                'name' => 'ADMIN',
                                'usableName' => 'admin',
                                'description' => null
                            ]
                        ]
                    ],
                ]
            ]);
    }
    
    public function testCannotGetAllUsersIfNotAdmin()
    {
        $user = User::create([
            'username' => "mr_robert",
            'first_name' => "Robert",
            'surname' => "Robert",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $this->actingAs($user);

        $response = $this->get('/api/admin/users');

        $response->assertStatus(402)
            ->assertExactJson([
                'status' => false,
                'message' => "Sorry! You cannot perform this action because you are not an administrator."
            ]);
    }
}
