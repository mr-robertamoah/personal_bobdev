<?php

namespace Tests\Feature;

use App\Enums\RelationshipTypeEnum;
use App\Models\Authorization;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase,
    WithFaker;

    protected function setUp() : void
    {
        parent::setUp();

        if (Schema::hasTable("users")) {
            DB::table("users")->truncate();
        }

        if (Schema::hasTable("authorizations")) {
            DB::table("authorizations")->truncate();
        }

        if (Schema::hasTable("permissions")) {
            DB::table("permissions")->truncate();
        }

        if (Schema::hasTable("roles")) {
            DB::table("roles")->truncate();
        }

        if (Schema::hasTable("companies")) {
            DB::table("companies")->truncate();
        }

        if (Schema::hasTable("projects")) {
            DB::table("projects")->truncate();
        }
    }

    public function testCannotAttachRoleToUserWhenSuperAdminWithoutAppropriateData()
    {
        $superAdmin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdminUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);

        $facilitatorUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $superAdmin->userTypes()->attach($superAdminUserType->id);
        $user->userTypes()->attach($facilitatorUserType->id);
        $creator->userTypes()->attach($facilitatorUserType->id);

        $authorizable = $creator->addedCompanies()->create([
            'name' => 'Company',
            'alias' => 'company',
        ]);

        $authorization = $creator->addedRoles()->create([
            'name' => $this->faker->name
        ]);

        $this->actingAs($superAdmin);

        $response = $this->postJson("/api/authorizations", []);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => true,
            "errors" => [
                "authorizable_type" => [],
                "authorizable_id" => [],
                "authorized_id" => [],
                "authorization_type" => [],
                "authorization_id" => []
            ]
        ]);
    }

    public function testCanAttachRoleToUserWhenSuperAdmin()
    {
        $superAdmin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdminUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);

        $facilitatorUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $superAdmin->userTypes()->attach($superAdminUserType->id);
        $user->userTypes()->attach($facilitatorUserType->id);
        $creator->userTypes()->attach($facilitatorUserType->id);

        $authorizable = $creator->addedCompanies()->create([
            'name' => 'Company',
            'alias' => 'company',
        ]);

        $authorization = $creator->addedRoles()->create([
            'name' => $this->faker->name
        ]);

        $this->actingAs($superAdmin);

        $response = $this->postJson("/api/authorizations", [
            "authorizable_type" => class_basename($authorizable),
            "authorizable_id" => (string) $authorizable->id,
            "authorization_type" => class_basename($authorization),
            "authorization_id" => (string) $authorization->id,
            "authorized_id" => (string) $user->id,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            "status" => true,
            "authorization" => [
                "authorizableType" => class_basename($authorizable),
                "authorizableId" => $authorizable->id,
                "authorizedId" => $user->id,
                "authorization" => [
                    "id" => $authorization->id,
                    "name" => $authorization->name,
                    "class" => is_null($authorization->class) ? null: class_basename($authorization->class),
                    "permissions" => []
                ]
            ]
        ]);
    }

    public function testCanAttachRoleToUserWhenOwnerOfAuthorizable()
    {
        $superAdmin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdminUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);

        $facilitatorUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $superAdmin->userTypes()->attach($superAdminUserType->id);
        $user->userTypes()->attach($facilitatorUserType->id);
        $creator->userTypes()->attach($facilitatorUserType->id);

        $authorizable = $creator->addedCompanies()->create([
            'name' => 'Company',
            'alias' => 'company',
        ]);
        $rel = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyMember->value
        ]);
        $rel->to()->associate($user);
        $rel->save();

        $authorization = $creator->addedRoles()->create([
            'name' => $this->faker->name
        ]);

        $this->actingAs($creator);

        $response = $this->postJson("/api/authorizations", [
            "authorizable_type" => class_basename($authorizable),
            "authorizable_id" => (string) $authorizable->id,
            "authorization_type" => class_basename($authorization),
            "authorization_id" => (string) $authorization->id,
            "authorized_id" => (string) $user->id,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            "status" => true,
            "authorization" => [
                "authorizableType" => class_basename($authorizable),
                "authorizableId" => $authorizable->id,
                "authorizedId" => $user->id,
                "authorization" => [
                    "id" => $authorization->id,
                    "name" => $authorization->name,
                    "class" => is_null($authorization->class) ? null: class_basename($authorization->class),
                    "permissions" => []
                ]
            ]
        ]);
    }

    public function testCannotDetachRoleToUserWhenSuperAdminWithoutValidAuthorizationId()
    {
        $superAdmin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdminUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);

        $facilitatorUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $superAdmin->userTypes()->attach($superAdminUserType->id);
        $user->userTypes()->attach($facilitatorUserType->id);
        $creator->userTypes()->attach($facilitatorUserType->id);

        $authorizable = $creator->addedCompanies()->create([
            'name' => 'Company',
            'alias' => 'company',
        ]);

        $creator->addedRoles()->create([
            'name' => $this->faker->name
        ]);

        $this->actingAs($superAdmin);

        $response = $this->delete("/api/authorizations/{100}");

        $response->assertStatus(422);
        $response->assertJson([
            "message" => 'Sorry! A valid authorization is required to perform this action.',
        ]);
    }

    public function testCanDetachRoleToUserWhenSuperAdmin()
    {
        $superAdmin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdminUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);

        $facilitatorUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $superAdmin->userTypes()->attach($superAdminUserType->id);
        $user->userTypes()->attach($facilitatorUserType->id);
        $creator->userTypes()->attach($facilitatorUserType->id);

        $authorizable = $creator->addedCompanies()->create([
            'name' => 'Company',
            'alias' => 'company',
        ]);

        $authorization = $creator->addedRoles()->create([
            'name' => $this->faker->name
        ]);

        $auth = $creator->authorizations()->create();
        $auth->authorizable()->associate($authorizable);
        $auth->authorization()->associate($authorization);
        $auth->authorized()->associate($user);
        $auth->save();

        $this->assertDatabaseHas("authorizations", [
            "user_id" => $creator->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
            "authorized_id" => $user->id,
            "authorized_type" => $user::class,
        ]);

        $this->actingAs($superAdmin);

        $response = $this->deleteJson("/api/authorizations/{$auth->id}");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
        ]);

        $this->assertDatabaseMissing("authorizations", [
            "user_id" => $creator->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
            "authorized_id" => $user->id,
            "authorized_type" => $user::class,
        ]);
    }

    public function testCanDetachRoleToUserWhenCreator()
    {
        $superAdmin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdminUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);

        $facilitatorUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $superAdmin->userTypes()->attach($superAdminUserType->id);
        $user->userTypes()->attach($facilitatorUserType->id);
        $creator->userTypes()->attach($facilitatorUserType->id);

        $authorizable = $creator->addedCompanies()->create([
            'name' => 'Company',
            'alias' => 'company',
        ]);

        $authorization = $creator->addedRoles()->create([
            'name' => $this->faker->name
        ]);

        $auth = $creator->authorizations()->create();
        $auth->authorizable()->associate($authorizable);
        $auth->authorization()->associate($authorization);
        $auth->authorized()->associate($user);
        $auth->save();

        $this->assertDatabaseHas("authorizations", [
            "user_id" => $creator->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
            "authorized_id" => $user->id,
            "authorized_type" => $user::class,
        ]);

        $this->actingAs($creator);

        $response = $this->deleteJson("/api/authorizations/{$auth->id}");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
        ]);

        $this->assertDatabaseMissing("authorizations", [
            "user_id" => $creator->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
            "authorized_id" => $user->id,
            "authorized_type" => $user::class,
        ]);
    }

    public function testCanDetachRoleToUserWhenAuthorized()
    {
        $superAdmin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdminUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);

        $facilitatorUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $superAdmin->userTypes()->attach($superAdminUserType->id);
        $user->userTypes()->attach($facilitatorUserType->id);
        $creator->userTypes()->attach($facilitatorUserType->id);

        $authorizable = $creator->addedCompanies()->create([
            'name' => 'Company',
            'alias' => 'company',
        ]);

        $authorization = $creator->addedRoles()->create([
            'name' => $this->faker->name
        ]);

        $auth = $creator->authorizations()->create();
        $auth->authorizable()->associate($authorizable);
        $auth->authorization()->associate($authorization);
        $auth->authorized()->associate($user);
        $auth->save();

        $this->assertDatabaseHas("authorizations", [
            "user_id" => $creator->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
            "authorized_id" => $user->id,
            "authorized_type" => $user::class,
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson("/api/authorizations/{$auth->id}");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
        ]);

        $this->assertDatabaseMissing("authorizations", [
            "user_id" => $creator->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
            "authorized_id" => $user->id,
            "authorized_type" => $user::class,
        ]);
    }

    public function testCannotGetAuthorizationsWithoutAuthorizableWhenNotAdmin()
    {
        $superAdmin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdminUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);

        $facilitatorUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $superAdmin->userTypes()->attach($superAdminUserType->id);
        $user->userTypes()->attach($facilitatorUserType->id);
        $creator->userTypes()->attach($facilitatorUserType->id);

        Authorization::factory()->count(13)
            ->has(Company::factory(), "authorizable")
            ->has(User::factory(), "authorized")
            ->has(Permission::factory(), "authorization")
            ->create([
                "user_id" => $creator->id
            ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/authorizations");

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "Sorry! A Company/Project must be given to perform this action.",
        ]);
    }

    public function testCannotGetAuthorizationsWhenNotAuthorized()
    {
        $superAdmin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdminUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);

        $facilitatorUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $superAdmin->userTypes()->attach($superAdminUserType->id);
        $user->userTypes()->attach($facilitatorUserType->id);
        $creator->userTypes()->attach($facilitatorUserType->id);

        $company = Company::factory()->create([
            "user_id" => $creator->id
        ]);

        $role = Role::factory()->create([
            "user_id" => $creator->id
        ]);

        $permission = Permission::factory()->create([
            "user_id" => $creator->id
        ]);

        $auths = Authorization::factory()->count(13)
            ->afterCreating(function ($auth) use ($company, $role, $permission) {
                $auth->authorizable()->associate($company);
                if ($auth->id % 2) $auth->authorization()->associate($role);
                else $auth->authorization()->associate($permission);
                $auth->authorized()->associate(
                    User::create([
                        'username' => $this->faker->userName,
                        'first_name' => $this->faker->firstName(),
                        'surname' => $this->faker->lastName(),
                        'password' => bcrypt("password"),
                        'email' => $this->faker->email(),
                    ])
                );
                $auth->save();
            })
            ->create([
                "user_id" => $creator->id
            ]);

        $this->actingAs($user);
        $company = $auths[0]->authorizable;

        $response = $this->getJson("/api/authorizations?authorizable_type=company&authorizable_id={$company->id}");

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "Sorry! You are not authorized to get the authorizations associated with {$company->name} Company.",
        ]);
    }

    public function testCanGetAuthorizationsWhenAdmin()
    {
        $superAdmin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdminUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);

        $facilitatorUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $superAdmin->userTypes()->attach($superAdminUserType->id);
        $user->userTypes()->attach($facilitatorUserType->id);
        $creator->userTypes()->attach($facilitatorUserType->id);

        $company = Company::factory()->create([
            "user_id" => $creator->id
        ]);

        $role = Role::factory()->create([
            "user_id" => $creator->id
        ]);

        $permission = Permission::factory()->create([
            "user_id" => $creator->id
        ]);

        $auths = Authorization::factory()->count(13)
            ->afterCreating(function ($auth) use ($company, $role, $permission) {
                $auth->authorizable()->associate($company);
                if ($auth->id % 2) $auth->authorization()->associate($role);
                else $auth->authorization()->associate($permission);
                $auth->authorized()->associate(
                    User::create([
                        'username' => $this->faker->userName,
                        'first_name' => $this->faker->firstName(),
                        'surname' => $this->faker->lastName(),
                        'password' => bcrypt("password"),
                        'email' => $this->faker->email(),
                    ])
                );
                $auth->save();
            })
            ->create([
                "user_id" => $creator->id
            ]);

        $this->actingAs($superAdmin);
        $company = $auths[0]->authorizable;

        $query = "authorizable_type=company&authorizable_id={$company->id}";
        $response = $this->getJson("/api/authorizations?" . $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => []
        ]);

        $data = json_decode($response->baseResponse->content());
        if (is_null($data->links->next)) {
            return;
        }

        $response = $this->getJson($data->links->next);
        
        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => []
        ]);
    }

    public function testCanGetAuthorizationsWhenOwner()
    {
        $superAdmin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdminUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);

        $facilitatorUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $superAdmin->userTypes()->attach($superAdminUserType->id);
        $user->userTypes()->attach($facilitatorUserType->id);
        $creator->userTypes()->attach($facilitatorUserType->id);

        $company = Company::factory()->create([
            "user_id" => $creator->id
        ]);

        $role = Role::factory()->create([
            "user_id" => $creator->id
        ]);

        $permission = Permission::factory()->create([
            "user_id" => $creator->id
        ]);

        $auths = Authorization::factory()->count(13)
            ->afterCreating(function ($auth) use ($company, $role, $permission) {
                $auth->authorizable()->associate($company);
                if ($auth->id % 2) $auth->authorization()->associate($role);
                else $auth->authorization()->associate($permission);
                $auth->authorized()->associate(
                    User::create([
                        'username' => $this->faker->userName,
                        'first_name' => $this->faker->firstName(),
                        'surname' => $this->faker->lastName(),
                        'password' => bcrypt("password"),
                        'email' => $this->faker->email(),
                    ])
                );
                $auth->save();
            })
            ->create([
                "user_id" => $creator->id
            ]);

        $this->actingAs($creator);
        $company = $auths[0]->authorizable;

        $query = "authorizable_type=company&authorizable_id={$company->id}";
        $response = $this->getJson("/api/authorizations?" . $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => []
        ]);

        $data = json_decode($response->baseResponse->content());
        if (is_null($data->links->next)) {
            return;
        }

        $response = $this->getJson($data->links->next);
        
        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => []
        ]);
    }

    public function testCanGetAuthorizationsWithoutAuthorizableWhenAdmin()
    {
        $superAdmin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $superAdminUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::SUPERADMIN
        ]);

        $facilitatorUserType = $superAdmin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $superAdmin->userTypes()->attach($superAdminUserType->id);
        $user->userTypes()->attach($facilitatorUserType->id);
        $creator->userTypes()->attach($facilitatorUserType->id);

        $company = Company::factory()->create([
            "user_id" => $creator->id
        ]);

        $role = Role::factory()->create([
            "user_id" => $creator->id
        ]);

        $permission = Permission::factory()->create([
            "user_id" => $creator->id
        ]);

        $auths = Authorization::factory()->count(13)
            ->afterCreating(function ($auth) use ($company, $role, $permission) {
                $auth->authorizable()->associate($company);
                if ($auth->id % 2) $auth->authorization()->associate($role);
                else $auth->authorization()->associate($permission);
                $auth->authorized()->associate(
                    User::create([
                        'username' => $this->faker->userName,
                        'first_name' => $this->faker->firstName(),
                        'surname' => $this->faker->lastName(),
                        'password' => bcrypt("password"),
                        'email' => $this->faker->email(),
                    ])
                );
                $auth->save();
            })
            ->create([
                "user_id" => $creator->id
            ]);

        $this->actingAs($superAdmin);
        $company = $auths[0]->authorizable;

        $query = "";
        $response = $this->getJson("/api/authorizations?" . $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => []
        ]);

        $data = json_decode($response->baseResponse->content());
        if (is_null($data->links->next)) {
            return;
        }

        $response = $this->getJson($data->links->next);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => []
        ]);
    }
}
