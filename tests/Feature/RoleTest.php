<?php

namespace Tests\Feature;

use App\Enums\PermissionEnum;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
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

    public function testCannotCreateRoleWhenNotAdminNotAuthorizedOrNotOwningAuthorizable()
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

        $response = $this->postJson("/api/roles", []);

        $response->assertStatus(403);
        $response->assertJson([
            "message" => true
        ]);
    }

    public function testCannotCreateRoleWithoutAppropriateData()
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

        $response = $this->postJson("/api/roles", []);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "The name field is required. (and 1 more error)",
            "errors" => [
                "name" => ["The name field is required."],
                "public" => ["The public field is required."],
            ]
        ]);
    }

    public function testCanCreateRoleWithAppropriateDataWhenSuperAdmin()
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

        $response = $this->postJson("/api/roles", $data);

        $response->assertStatus(201);
        $response->assertJson([
            "status" => true,
            "role" => [
                "id" => 1,
                ...$data
            ]
        ]);
    }

    public function testCanCreateRoleWithAppropriateDataWhenOwnerOfAuthorizable()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);

        Company::factory()->create([
            "user_id" => $user->id
        ]);
        
        $this->actingAs($user);

        $data = [
            "name" => $this->faker->name(),
            "public" => $this->faker->boolean(),
            "description" => $this->faker->sentence(),
            "class" => null,
        ];

        $response = $this->postJson("/api/roles", $data);

        $response->assertStatus(201);
        $response->assertJson([
            "status" => true,
            "role" => $data
        ]);
    }

    public function testCanCreateRoleWithAppropriateDataWhenAuthorized()
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
            "name" => PermissionEnum::CREATEROLES->value
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

        $response = $this->postJson("/api/roles", $data);

        $response->assertStatus(201);
        $response->assertJson([
            "status" => true,
            "role" => $data
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

        $role = Role::factory()->create([
            "user_id" => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/roles/{$role->id}", []);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => true
        ]);
    }

    public function testCannotUpdateRoleWithoutAnyData()
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

        $role = Role::factory()->create([
            "user_id" => $user->id,
        ]);

        $authorization = $admin->authorizations()->create();
        $authorization->authorized()->associate($user);
        $authorization->authorization()->associate($role);
        $authorization->save();

        $this->actingAs($user);

        $response = $this->postJson("/api/roles/{$role->id}", []);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => true
        ]);
    }

    public function testCanUpdateRoleWhenAdmin()
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

        $role = Role::factory()->create([
            "user_id" => $superAdmin->id,
            "public" => true
        ]);

        $this->actingAs($admin);

        $data = [
            "name" => "new name",
            "public" => false
        ];
        $response = $this->postJson("/api/roles/{$role->id}", $data);

        $response->assertStatus(201);
        $response->assertJson([
            "status" => true,
            "role" => $data
        ]);

        $this->assertDatabaseHas("roles", $data);
    }

    public function testCanUpdateRoleWhenSuperAdmin()
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

        $role = Role::factory()->create([
            "user_id" => $superAdmin->id,
            "public" => true
        ]);

        $this->actingAs($other);

        $data = [
            "name" => "new name",
            "public" => false
        ];
        $response = $this->postJson("/api/roles/{$role->id}", $data);

        $response->assertStatus(201);
        $response->assertJson([
            "status" => true,
            "role" => $data
        ]);

        $this->assertDatabaseHas("roles", $data);
    }

    public function testCanUpdateRoleWhenOwner()
    {
        $other = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $other->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $other->userTypes()->attach($userType->id);

        $role = Role::factory()->create([
            "user_id" => $other->id,
        ]);

        $this->actingAs($other);

        $data = [
            "name" => "new name",
            "public" => false
        ];
        $response = $this->postJson("/api/roles/{$role->id}", $data);

        $response->assertStatus(201);
        $response->assertJson([
            "status" => true,
            "role" => $data
        ]);

        $this->assertDatabaseHas("roles", $data);
    }

    public function testCannotDeleteRoleWhenNotAuthorized()
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

        $role = Role::factory()->create([
            "user_id" => $admin->id,
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson("/api/roles/{$role->id}", []);

        $response->assertStatus(422);
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

        $role = Role::factory()->create([
            "user_id" => $superAdmin->id,
            "public" => true
        ]);

        $this->actingAs($superAdmin);  
        
        $response = $this->deleteJson("/api/roles/{$role->id}");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
        ]);

        $this->assertDatabaseMissing("roles", [
            "id" => $role->id
        ]);
    }

    public function testCanDeletePermissionWhenAdmin()
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

        $role = Role::factory()->create([
            "user_id" => $user->id,
            "public" => true
        ]);

        $this->actingAs($admin);  
        
        $response = $this->deleteJson("/api/roles/{$role->id}");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
        ]);

        $this->assertDatabaseMissing("roles", [
            "id" => $role->id
        ]);
    }

    public function testCanDeletePrivateRoleWhenOwner()
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

        $this->actingAs($user);

        $role = Role::factory()->create([
            "user_id" => $user->id,
            "public" => false
        ]);

        $response = $this->deleteJson("/api/roles/{$role->id}");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
        ]);

        $this->assertDatabaseMissing("roles", [
            "id" => $role->id
        ]);
    }

    public function testCannotDeletePublicRoleWhenOwner()
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
            "public" => false
        ]);

        $this->actingAs($user);

        $role = Role::factory()->create([
            "user_id" => $user->id,
        ]);

        $response = $this->deleteJson("/api/roles/{$role->id}");

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "Sorry! You are not authorized to update/delete role with name: {$role->name}.",
        ]);

        $this->assertDatabaseHas("roles", [
            "id" => $role->id
        ]);
    }

    public function testCannotGetRolesWithoutValidQueryWhenNotAdmin()
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

        $roles = Role::factory()->count(13)
            ->create([
                "user_id" => $creator->id
            ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/roles");

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "Sorry! You are required to provide at least name, like or class.",
        ]);
    }

    public function testCanGetRolesWithoutValidQueryWhenAdmin()
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

        $roles = Role::factory()->count(13)
            ->create([
                "user_id" => $creator->id
            ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/roles");

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => []
        ]);
        $this->assertEquals(
            json_decode($response->baseResponse->content())->meta->total,
            count($roles)
        );
    }

    public function testCanGetPublicRolesWithValidQueryWhenNotAuthorized()
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

        $roles = Role::factory()->count(13)
            ->create([
                "user_id" => $creator->id
            ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/roles?class=company");

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => []
        ]);
        $this->assertEquals(
            json_decode($response->baseResponse->content())->meta->total,
            count(array_filter($roles->toArray(), function($value) {
                return $value["class"] == "App\\Models\\Company" && $value["public"];
            }))
        );
    }

    public function testCanGetPublicAndCreatedPrivateRolesWithValidQueryWhenNotAuthorized()
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

        $roles = Role::factory()->count(13)
            ->create([
                "user_id" => $creator->id
            ]);

        $createdRoles = Role::factory()->count(3)
            ->create([
                "user_id" => $user->id,
                "public" => 0,
                "class" => Company::class
            ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/roles?class=company");

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => []
        ]);
        $this->assertEquals(
            json_decode($response->baseResponse->content())->meta->total,
            count(array_unique(array_merge(array_filter($roles->toArray(), function($value) {
                return $value["class"] == "App\\Models\\Company" && $value["public"];
            }), $createdRoles->toArray()), SORT_REGULAR))
        );
    }

    public function testCanGetRolesWithPermissionNameQueryWhenNotAuthorized()
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

        $roles = Role::factory()->count(13)
            ->create([
                "user_id" => $creator->id,
                "public" => 1,
                "class" => Company::class
            ]);

        $permission = Permission::factory()
            ->create([
                "user_id" => $creator->id
            ]);

        $createdRoles = Role::factory()->count(3)
            ->create([
                "user_id" => $user->id,
                "public" => 0,
                "class" => Company::class
            ]);

        $roles[0]->permissions()->attach($permission);
        $createdRoles[0]->permissions()->attach($permission);

        $this->actingAs($user);

        $response = $this->getJson("/api/roles?class=company&permission_name={$permission->name}");

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => []
        ]);
        $this->assertEquals(
            json_decode($response->baseResponse->content())->meta->total,
            2
        );
    }

    public function testCanGetRolesWithLikeQueryWhenNotAuthorized()
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

        $roles = Role::factory()->count(13)
            ->create([
                "user_id" => $creator->id,
                "public" => 1
            ]);

        $description = "a good one";
        $permission = Permission::factory()
            ->create([
                "user_id" => $creator->id,
                "description" => "this is quite " . $description
            ]);

        $createdRoles = Role::factory()->count(3)
            ->create([
                "user_id" => $user->id,
                "public" => 0,
                "class" => Company::class
            ]);

        $roles[0]->permissions()->attach($permission);
        $createdRoles[0]->permissions()->attach($permission);

        $this->actingAs($user);

        $response = $this->getJson("/api/roles?like={$description}");

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => []
        ]);
        $this->assertEquals(
            json_decode($response->baseResponse->content())->meta->total,
            2
        );
    }

    public function testCannotAttachPermissionsToRoleWithoutPermissions()
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

        $role = Role::factory()
            ->create([
                "user_id" => $creator->id,
                "public" => 1
            ]);

        $permission = Permission::factory()
            ->create([
                "user_id" => $creator->id,
                "description" => "this is quite good"
            ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/roles/{$role->id}/sync");

        $response->assertStatus(422);
        $response->assertJson([
            "message" => true,
        ]);
    }

    public function testCannotAttachPermissionsToRoleWithEmptyPermissions()
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

        $role = Role::factory()
            ->create([
                "user_id" => $creator->id,
                "public" => 1
            ]);

        $permission = Permission::factory()
            ->create([
                "user_id" => $creator->id,
                "description" => "this is quite good"
            ]);

        $this->actingAs($user);

        $data = [
            "permission_ids" => []
        ];
        $response = $this->postJson("/api/roles/{$role->id}/sync", $data);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => 'The permission ids field is required.',
        ]);
    }

    public function testCannotAttachPermissionsToRoleWithOnlyInvalidPermissions()
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

        $role = Role::factory()
            ->create([
                "user_id" => $creator->id,
                "public" => 1
            ]);

        $this->actingAs($creator);

        $data = [
            "permission_ids" => [1, 2]
        ];
        $response = $this->postJson("/api/roles/{$role->id}/sync", $data);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "Sorry! all permissions provided do not exist.",
        ]);
    }

    public function testCannotAttachPermissionsToRoleWithSomeInvalidPermissions()
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

        $role = Role::factory()
            ->create([
                "user_id" => $creator->id,
                "public" => 1
            ]);

        $permission = Permission::factory()
            ->create([
                "user_id" => $creator->id,
                "public" => 1
            ]);

        $this->actingAs($creator);

        $data = [
            "permission_ids" => [$permission->id, 20]
        ];
        $response = $this->postJson("/api/roles/{$role->id}/sync", $data);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "Sorry! Permissions with [20] ids are not valid.",
        ]);
    }

    public function testCannotAttachPermissionsToRoleWhenPermissionsHaveIncompatibleClasses()
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

        $role = Role::factory()
            ->create([
                "user_id" => $creator->id,
                "public" => 1,
                "class" => Company::class
            ]);

        $permission = Permission::factory()
            ->create([
                "user_id" => $creator->id,
                "public" => 1,
                "class" => Project::class
            ]);

        $this->actingAs($creator);

        $data = [
            "permission_ids" => [$permission->id]
        ];
        $response = $this->postJson("/api/roles/{$role->id}/sync", $data);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "Sorry! Permissions with [{$permission->id}] ids do not have valid class to be attached to the role.",
        ]);
    }

    public function testCannotAttachPermissionsToInvalidRoleWithoutPermissions()
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

        $role = Role::factory()
            ->create([
                "user_id" => $creator->id,
                "public" => 1
            ]);

        $permission = Permission::factory()
            ->create([
                "user_id" => $creator->id,
                "description" => "this is quite good"
            ]);

        $this->actingAs($user);

        $data = [$permission->id];

        $response = $this->postJson("/api/roles/{$role->id}/sync", $data);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => true,
        ]);
    }

    public function testCanAttachPermissionsToRoleWhenAdmin()
    {
        $admin = User::create([
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

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $role = Role::factory()
            ->create([
                "user_id" => $creator->id,
                "public" => 1,
                "class" => Company::class
            ]);

        $permission = Permission::factory()
            ->create([
                "user_id" => $creator->id,
                "description" => "this is quite good",
                "class" => Company::class
            ]);

        $this->actingAs($admin);
        
        $data = ["permission_ids" => [$permission->id]];

        $response = $this->postJson("/api/roles/{$role->id}/sync", $data);

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
            "role" => [
                "id" => $role->id,
                "permissions" => [
                    ["id" => $permission->id]
                ]
            ]
        ]);
    }

    public function testCanAttachPermissionsToRoleWhenOwner()
    {
        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $role = Role::factory()
            ->create([
                "user_id" => $creator->id,
                "public" => 1,
                "class" => Company::class
            ]);

        $permission = Permission::factory()
            ->create([
                "user_id" => $creator->id,
                "description" => "this is quite good",
                "class" => Company::class
            ]);

        $this->actingAs($creator);
        
        $data = ["permission_ids" => [$permission->id]];

        $response = $this->postJson("/api/roles/{$role->id}/sync", $data);

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
            "role" => [
                "id" => $role->id,
                "permissions" => [
                    ["id" => $permission->id]
                ]
            ]
        ]);

        $this->assertDatabaseHas("permission_role", [
            "permission_id" => $permission->id,
            "role_id" => $role->id,
        ]);
    }
}
