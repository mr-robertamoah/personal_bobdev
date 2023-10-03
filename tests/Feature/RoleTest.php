<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase,
    WithFaker;

    protected function setUp() : void
    {
        parent::setUp();

        if (Schema::hasTable("users")) {
            DB::table("users")->truncate();
        }

        if (Schema::hasTable("permissions")) {
            DB::table("permissions")->truncate();
        }

        if (Schema::hasTable("roles")) {
            DB::table("roles")->truncate();
        }
    }

    public function testCannotCreatePermissionWhenNotAuthorized()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $facilitatorUserType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $user->userTypes()->attach($facilitatorUserType->id);

        $this->actingAs($user);

        $response = $this->postJson("/api/permissions", []);

        $response->assertStatus(403);
        $response->assertJson([
            "message" => true
        ]);
    }

    public function testCannotCreatePermissionWithoutAppropriateData()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);
        
        $user->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $response = $this->postJson("/api/permissions", []);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "The name field is required. (and 1 more error)",
            "errors" => [
                "name" => ["The name field is required."],
                "public" => ["The public field is required."],
            ]
        ]);
    }

    public function testCanCreatePermissionWithAppropriateDataWhenSuperAdmin()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);
        
        $user->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $data = [
            "name" => $this->faker->name(),
            "public" => $this->faker->boolean(),
            "description" => $this->faker->sentence(),
            "class" => null,
        ];

        $response = $this->postJson("/api/permissions", $data);

        $response->assertStatus(201);
        $response->assertJson([
            "status" => true,
            "permission" => [
                "id" => 1,
                ...$data
            ]
        ]);
    }

    public function testCanCreatePermissionWithAppropriateDataWhenAuthorizedButNotSuperAdmin()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);
        
        $authorized = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $authorized->userTypes()->attach($userType->id);

        $permission = Permission::factory()->create([
            "user_id" => $user->id,
            "name" => PermissionEnum::CREATEPERMISSIONS->value
        ]);

        $authorization = $user->authorizations()->create();
        $authorization->authorized()->associate($authorized);
        $authorization->authorization()->associate($permission);
        $authorization->save();
        
        $this->actingAs($authorized);

        $data = [
            "name" => $this->faker->name(),
            "public" => $this->faker->boolean(),
            "description" => $this->faker->sentence(),
            "class" => null,
        ];

        $response = $this->postJson("/api/permissions", $data);

        $response->assertStatus(201);
        $response->assertJson([
            "status" => true,
            "permission" => $data
        ]);
    }

    public function testCannotUpdatePermissionWhenNotAuthorized()
    {
        $admin = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $user->userTypes()->attach($userType->id);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);
        
        $admin->userTypes()->attach($userType->id);

        $permission = Permission::factory()->create([
            "user_id" => $user->id,
            "name" => PermissionEnum::CREATEPERMISSIONS->value
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/permissions/{$permission->id}", []);

        $response->assertStatus(403);
        $response->assertJson([
            "message" => true
        ]);
    }

    public function testCannotUpdatePermissionWithoutAnyData()
    {
        $admin = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $user->userTypes()->attach($userType->id);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);
        
        $admin->userTypes()->attach($userType->id);

        $permission = Permission::factory()->create([
            "user_id" => $user->id,
            "name" => PermissionEnum::CREATEPERMISSIONS->value
        ]);

        $authorization = $admin->authorizations()->create();
        $authorization->authorized()->associate($user);
        $authorization->authorization()->associate($permission);
        $authorization->save();

        $this->actingAs($user);

        $response = $this->postJson("/api/permissions/{$permission->id}", []);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => true
        ]);
    }

    public function testCanUpdatePermissionWhenAdmin()
    {
        $admin = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdmin = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);
        
        $superAdmin->userTypes()->attach($userType->id);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);
        
        $admin->userTypes()->attach($userType->id);

        $permission = Permission::factory()->create([
            "user_id" => $superAdmin->id,
            "public" => true
        ]);

        $this->actingAs($admin);

        $data = [
            "name" => "new name",
            "public" => false
        ];
        $response = $this->postJson("/api/permissions/{$permission->id}", $data);

        $response->assertStatus(201);
        $response->assertJson([
            "status" => true,
            "permission" => $data
        ]);

        $this->assertDatabaseHas("permissions", $data);
    }

    public function testCanUpdatePermissionWhenSuperAdmin()
    {
        $other = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdmin = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $other->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);
        
        $superAdmin->userTypes()->attach($userType->id);
        
        $other->userTypes()->attach($userType->id);

        $permission = Permission::factory()->create([
            "user_id" => $superAdmin->id,
            "public" => true
        ]);

        $this->actingAs($other);

        $data = [
            "name" => "new name",
            "public" => false
        ];
        $response = $this->postJson("/api/permissions/{$permission->id}", $data);

        $response->assertStatus(201);
        $response->assertJson([
            "status" => true,
            "permission" => $data
        ]);

        $this->assertDatabaseHas("permissions", $data);
    }

    public function testCanUpdatePermissionWhenAuthorized()
    {
        $other = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdmin = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $other->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);
        
        $superAdmin->userTypes()->attach($userType->id);

        $userType = $other->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $other->userTypes()->attach($userType->id);

        $permission = Permission::factory()->create([
            "user_id" => $superAdmin->id,
            "name" => PermissionEnum::CREATEPERMISSIONS->value
        ]);

        $authorization = $superAdmin->authorizations()->create();
        $authorization->authorized()->associate($other);
        $authorization->authorization()->associate($permission);
        $authorization->save();

        $this->actingAs($other);

        $data = [
            "name" => "new name",
            "public" => false
        ];
        $response = $this->postJson("/api/permissions/{$permission->id}", $data);

        $response->assertStatus(201);
        $response->assertJson([
            "status" => true,
            "permission" => $data
        ]);

        $this->assertDatabaseHas("permissions", $data);
    }

    public function testCannotDeletePermissionWhenNotAuthorized()
    {
        $admin = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $user->userTypes()->attach($userType->id);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);
        
        $admin->userTypes()->attach($userType->id);

        $permission = Permission::factory()->create([
            "user_id" => $admin->id,
            "name" => PermissionEnum::CREATEPERMISSIONS->value
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson("/api/permissions/{$permission->id}", []);

        $response->assertStatus(403);
        $response->assertJson([
            "message" => true
        ]);
    }

    public function testCannotDeletePermissionWhenNotAdmin()
    {
        $admin = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);
        
        $user->userTypes()->attach($userType->id);
        
        $admin->userTypes()->attach($userType->id);

        $permission = Permission::factory()->create([
            "user_id" => $admin->id,
            "name" => PermissionEnum::CREATEPERMISSIONS->value
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson("/api/permissions/{$permission->id}", []);

        $response->assertStatus(403);
        $response->assertJson([
            "message" => true
        ]);
    }

    public function testCanDeletePermissionWhenSuperAdmin()
    {
        $admin = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdmin = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);
        
        $superAdmin->userTypes()->attach($userType->id);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);
        
        $admin->userTypes()->attach($userType->id);

        $permission = Permission::factory()->create([
            "user_id" => $superAdmin->id,
            "public" => true
        ]);

        $this->actingAs($superAdmin);  
        
        $response = $this->deleteJson("/api/permissions/{$permission->id}");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
        ]);

        $this->assertDatabaseMissing("permissions", [
            "id" => $permission->id
        ]);
    }

    public function testCanDeletePermissionWhenAuthorized()
    {
        $admin = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);
        
        $admin->userTypes()->attach($userType->id);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $user->userTypes()->attach($userType->id);

        $permission = Permission::factory()->create([
            "user_id" => $admin->id,
            "public" => true
        ]);

        $this->actingAs($user);

        $permission = Permission::factory()->create([
            "user_id" => $user->id,
            "name" => PermissionEnum::CREATEPERMISSIONS->value
        ]);

        $authorization = $user->authorizations()->create();
        $authorization->authorized()->associate($user);
        $authorization->authorization()->associate($permission);
        $authorization->save();
        
        $permission = Permission::factory()->create([
            "user_id" => $user->id
        ]);

        $response = $this->deleteJson("/api/permissions/{$permission->id}");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
        ]);

        $this->assertDatabaseMissing("permissions", [
            "id" => $permission->id
        ]);
    }

    public function testCannotGetPermissionsWithoutValidQueryWhenNotAdmin()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType->id);

        $permissions = Permission::factory()->count(13)
            ->create([
                "user_id" => $creator->id
            ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/permissions");

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "Sorry! You are required to provide at least name, like or class.",
        ]);
    }

    public function testCanGetPermissionsWithoutValidQueryWhenAdmin()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $permissions = Permission::factory()->count(13)
            ->create([
                "user_id" => $creator->id
            ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/permissions");

        ds($response->baseResponse->content(), $permissions);
        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => []
        ]);
        $this->assertEquals(
            json_decode($response->baseResponse->content())->meta->total,
            count($permissions)
        );
    }

    public function testCanGetPublicPermissionsWithValidQueryWhenNotAuthorized()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType->id);

        $permissions = Permission::factory()->count(13)
            ->create([
                "user_id" => $creator->id
            ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/permissions?class=company");

        ds($response->baseResponse->content(), $permissions);
        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => []
        ]);
        $this->assertEquals(
            json_decode($response->baseResponse->content())->meta->total,
            count(array_filter($permissions->toArray(), function($value) {
                return $value["class"] == "App\\Models\\Company" && $value["public"];
            }))
        );
    }

    public function testCanGetPublicAndCreatedPrivatePermissionsWithValidQueryWhenNotAuthorized()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType->id);

        $permissions = Permission::factory()->count(13)
            ->create([
                "user_id" => $creator->id
            ]);

        $createdPermissions = Permission::factory()->count(3)
            ->create([
                "user_id" => $user->id,
                "public" => 0,
                "class" => Company::class
            ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/permissions?class=company");

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => []
        ]);
        $this->assertEquals(
            json_decode($response->baseResponse->content())->meta->total,
            count(array_unique(array_merge(array_filter($permissions->toArray(), function($value) {
                return $value["class"] == "App\\Models\\Company" && $value["public"];
            }), $createdPermissions->toArray()), SORT_REGULAR))
        );
    }

    // get test where permissionname and like queries and sync
}
