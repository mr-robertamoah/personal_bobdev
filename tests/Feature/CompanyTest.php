<?php

namespace Tests\Feature;

use App\DTOs\CompanyDTO;
use App\Enums\PaginationEnum;
use App\Enums\ProjectParticipantEnum;
use App\Enums\RelationshipTypeEnum;
use App\Exceptions\CompanyException;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use App\Models\UserType;
use App\Services\CompanyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testCannotCreateCompanyWithoutAnAlias()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $response = $this->postJson('/api/company/create', [
            'name' => 'the great enterprise',
        ]);

        $response->assertStatus(422);
    }

    public function testCannotCreateCompanyWithoutAnName()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $response = $this->postJson('/api/company/create', [
            'alias' => 'enterprise',
        ]);

        $response->assertStatus(422);
    }

    public function testCannotCreateCompanyWithoutBeingAnAdultUser()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $this->actingAs($user);
        
        $response = $this->postJson('/api/company/create', [
            'name' => 'the great enterprise',
        ]);

        $response->assertStatus(403);
    }

    public function testCanCreateCompanyIfAnAdult()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $response = $this->postJson('/api/company/create', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'company' => [
                    'name' => 'the great enterprise',
                ]
            ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);
    }

    public function testCannotUpdateInvalidCompany()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $admin = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $admin->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->actingAs($admin);

        $response = $this->postJson("/api/company/10/update", [
            'name' => 'the great ent.',
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => "Sorry! You need to provide a company to be able to perform this action.",
                'exception' => CompanyException::class
            ]);

        $this->assertDatabaseMissing('companies', [
            'name' => 'the great ent.',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);
    }

    public function testCannotUpdateWithInadequateData()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $admin = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->actingAs($admin);

        $response = $this->postJson("/api/company/{$company->id}/update");

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => "Sorry! There is not enough data to update the information the company with name {$company->name}.",
                'exception' => CompanyException::class
            ]);
    }

    public function testCannotUpdateCompanyIfNotAuthorized()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $admin = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $admin->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->actingAs($admin);

        $response = $this->postJson("/api/company/{$company->id}/update", [
            'name' => 'the great ent.',
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => "Sorry! You are not authorized to perform this action on company with name {$company->name}.",
                'exception' => CompanyException::class
            ]);

        $this->assertDatabaseMissing('companies', [
            'name' => 'the great ent.',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);
    }

    public function testCanUpdateCompanyIfCompanyOwner()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/company/{$company->id}/update", [
            'name' => 'the great ent.',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'company' => [
                    'name' => 'the great ent.',
                ]
            ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'the great ent.',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);
    }

    public function testCanUpdateCompanyIfAnAdmin()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $admin = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->actingAs($admin);

        $response = $this->postJson("/api/company/{$company->id}/update", [
            'name' => 'the great ent.',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'company' => [
                    'name' => 'the great ent.',
                ]
            ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'the great ent.',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);
    }

    public function testCanUpdateCompanyIfManager()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $manager = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $manager->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $manager->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($manager);
        $relation->save();

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->assertDatabaseHas('relations', [
            'to_id' => $manager->id,
            'to_type' => $manager::class,
            'by_id' => $company->id,
            'by_type' => $company::class,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->actingAs($manager);

        $response = $this->postJson("/api/company/{$company->id}/update", [
            'name' => 'the great ent.',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'company' => [
                    'name' => 'the great ent.',
                ]
            ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'the great ent.',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);
    }

    public function testCannotDeleteInvalidCompany()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $admin = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $admin->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->actingAs($admin);

        $response = $this->deleteJson("/api/company/10");

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => "Sorry! You need to provide a company to be able to perform this action.",
                'exception' => CompanyException::class
            ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);
    }

    public function testCannotDeleteCompanyIfNotAuthorized()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $otherUser = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $otherUser->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $otherUser->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->actingAs($otherUser);

        $response = $this->deleteJson("/api/company/{$company->id}");

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => "Sorry! You are not authorized to perform this action on company with name {$company->name}.",
                'exception' => CompanyException::class
            ]);

        $this->assertDatabaseMissing('companies', [
            'name' => 'the great ent.',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);
    }

    public function testCanDeleteCompanyIfAnAdmin()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $admin = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->actingAs($admin);

        $response = $this->deleteJson("/api/company/{$company->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                "status" => true
            ]);

        $this->assertSoftDeleted('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);
    }

    public function testCanDeleteCompanyIfCompanyOwner()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $admin = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson("/api/company/{$company->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                "status" => true
            ]);

        $this->assertSoftDeleted('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'delete',
            'performedby_id' => $user->id,
            'performedby_type' => $user::class,
            'performedon_id' => $company->id,
            'performedon_type' => $company::class,
        ]);
    }

    public function testCannotAddMultipleUsersAsMembersToCompanyWhenNotAuthorized()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member2 = User::create([
            'username' => "membertwo",
            'first_name' => "John",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "membertwo@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member2->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->actingAs($other);

        $response = $this->postJson("/api/company/{$company->id}/add_members", [
            'memberships' => [$member1->id => 'member', $member2->id => 'member']
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                "exception" => CompanyException::class,
                'message' => "Sorry! You are not authorized to perform this action on company with name {$company->name}."
            ]);
        
        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        
        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCannotAddMultipleUsersAsAdministratorsToCompanyWhenNotAnAdminOrCompanyOwner()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member2 = User::create([
            'username' => "membertwo",
            'first_name' => "John",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "membertwo@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member2->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($other);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->actingAs($other);

        $response = $this->postJson("/api/company/{$company->id}/add_members", [
            'memberships' => [$member1->id => 'administrator', $member2->id => 'administrator']
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                "exception" => CompanyException::class,
                'message' => "Sorry! You are not authorized to perform this action on the company with name {$company->name}."
            ]);
        
        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        
        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
    }

    public function testCanSendMemberRequestsToMultipleUsersWhenAnAdmin()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member2 = User::create([
            'username' => "membertwo",
            'first_name' => "John",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "membertwo@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member2->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $admin = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->actingAs($admin);

        $response = $this->postJson("/api/company/{$company->id}/add_members", [
            'memberships' => [$member1->id => 'member', $member2->id => 'member']
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                "status" => true
            ]);
        
        $this->assertDatabaseHas('requests', [
            'from_type' => $admin::class,
            'from_id' => $admin->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'type' => RelationshipTypeEnum::companyMember->value
        ]);
        
        $this->assertDatabaseHas('requests', [
            'from_type' => $admin::class,
            'from_id' => $admin->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCanSendMemberRequestToMultipleUsersWhenCompanyOwner()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member2 = User::create([
            'username' => "membertwo",
            'first_name' => "John",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "membertwo@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member2->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $admin = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/company/{$company->id}/add_members", [
            'memberships' => [$member1->id => 'member', $member2->id => 'member']
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                "status" => true
            ]);
        
        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'type' => RelationshipTypeEnum::companyMember->value
        ]);
        
        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCanSendAdministratorRequestToMultipleUsersWhenCompanyOwner()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member2 = User::create([
            'username' => "membertwo",
            'first_name' => "John",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "membertwo@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member2->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $admin = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/company/{$company->id}/add_members", [
            'memberships' => [$member1->id => 'administrator', $member2->id => 'administrator']
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                "status" => true
            ]);
        
        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        
        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
    }

    public function testCanSendAdministratorRequestsToMultipleUsersWhenCompanyOwnerOrAdmin()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member2 = User::create([
            'username' => "membertwo",
            'first_name' => "John",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "membertwo@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member2->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $admin = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/company/{$company->id}/add_members", [
            'memberships' => [$member1->id => 'administrator', $member2->id => 'member']
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                "status" => true
            ]);
        
        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        
        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCanSendMemberRequestsToMultipleUsersWhenCompanyAdministrator()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member2 = User::create([
            'username' => "membertwo",
            'first_name' => "John",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "membertwo@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member2->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $admin = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $admin->userTypes()->attach($userType->id);

        $companyAdmin = User::create([
            'username' => "companyadmin",
            'first_name' => "Collins",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "companyadmin@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $companyAdmin->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($companyAdmin);
        $relation->save();
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $companyAdmin::class,
            'to_id' => $companyAdmin->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->actingAs($companyAdmin);

        $response = $this->postJson("/api/company/{$company->id}/add_members", [
            'relationshipType' => 'member',
            'memberships' => [$member1->id => 'member', $member2->id => 'member']
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                "status" => true
            ]);
        
        $this->assertDatabaseHas('requests', [
            'from_type' => $companyAdmin::class,
            'from_id' => $companyAdmin->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'type' => RelationshipTypeEnum::companyMember->value
        ]);
        
        $this->assertDatabaseHas('requests', [
            'for_type' => $company::class,
            'for_id' => $company->id,
            'from_type' => $companyAdmin::class,
            'from_id' => $companyAdmin->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCannotRemoveMembersFromCompanyWhenNotAuthorized()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member2 = User::create([
            'username' => "membertwo",
            'first_name' => "John",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "membertwo@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member2->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member1);
        $relation->save();
        
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member2);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->actingAs($other);

        $response = $this->postJson("/api/company/{$company->id}/remove_members", [
            'memberships' => [$member1->id => 'member', $member2->id => 'member']
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                "exception" => CompanyException::class,
                'message' => "Sorry! You are not authorized to perform this action on company with name {$company->name}."
            ]);
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCannotRemoveMembersFromInvalidCompany()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member2 = User::create([
            'username' => "membertwo",
            'first_name' => "John",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "membertwo@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member2->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member1);
        $relation->save();

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member2);
        $relation->save();
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/company/10/remove_members", [
            'memberships' => [$member1->id => 'member', $member2->id => 'member']
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                "exception" => CompanyException::class,
                'message' => "Sorry! You need to provide a company to be able to perform this action."
            ]);
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCannotRemoveAdministratorsFromCompanyWhenCompanyAdministator()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member2 = User::create([
            'username' => "membertwo",
            'first_name' => "John",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "membertwo@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member2->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $other->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $other->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($member1);
        $relation->save();

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($member2);
        $relation->save();
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->actingAs($member1);

        $response = $this->postJson("/api/company/{$company->id}/remove_members", [
            'memberships' => [$member2->id => 'administrator']
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                "exception" => CompanyException::class,
                'message' => "Sorry! You are not authorized to perform this action on the company with name {$company->name}"
            ]);
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
    }

    public function testCanRemoveMembersAndAdministratorsFromCompanyWhenAdmin()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member2 = User::create([
            'username' => "membertwo",
            'first_name' => "John",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "membertwo@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member2->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $other->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $other->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($member1);
        $relation->save();

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member2);
        $relation->save();
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->actingAs($other);

        $response = $this->postJson("/api/company/{$company->id}/remove_members", [
            'memberships' => [$member1->id => 'administrator', $member2->id => 'member']
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                "status" => true
            ]);
        
        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        
        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCanRemoveMembersAndAdministratorsFromCompanyWhenCompanyOwner()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member2 = User::create([
            'username' => "membertwo",
            'first_name' => "John",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "membertwo@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member2->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $other->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $other->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($member1);
        $relation->save();

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member2);
        $relation->save();
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/company/{$company->id}/remove_members", [
            'memberships' => [$member1->id => 'administrator', $member2->id => 'member']
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                "status" => true
            ]);
        
        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        
        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCannotLeaveCompanyWhenOwner()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member2 = User::create([
            'username' => "membertwo",
            'first_name' => "John",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "membertwo@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member2->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($member1);
        $relation->save();

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member2);
        $relation->save();
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/company/{$company->id}/leave", [
            'relationshipType' => 'administrator'
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                "exception" => CompanyException::class,
                'message' => "Sorry! {$user->name} is the owner and is not a member."
            ]);
    }

    public function testCannotLeaveCompanyWhenNotMember()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member2 = User::create([
            'username' => "membertwo",
            'first_name' => "John",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "membertwo@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member2->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($member1);
        $relation->save();
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        
        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member2::class,
            'to_id' => $member2->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->actingAs($member2);

        $response = $this->postJson("/api/company/{$company->id}/leave", [
            'relationshipType' => 'member'
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                "exception" => CompanyException::class,
                'message' => "Sorry! {$member2->name} must be a member of {$company->name} company."
            ]);
    }

    public function testCanLeaveCompanyWhenMember()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member1);
        $relation->save();
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->actingAs($member1);

        $response = $this->postJson("/api/company/{$company->id}/leave", [
            'relationshipType' => 'member'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                "status" => true,
                'company' => [
                    'name' => 'the great enterprise'
                ]
            ]);

        $this->assertTrue($company->refresh()->isNotMember($member1));
    }

    public function testCanLeaveCompanyWhenAdministrator()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $member1 = User::create([
            'username' => "memberone",
            'first_name' => "Joshua",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "memberone@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $member1->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'the great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($member1);
        $relation->save();
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member1::class,
            'to_id' => $member1->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->actingAs($member1);

        $response = $this->postJson("/api/company/{$company->id}/leave", [
            'relationshipType' => 'administrator'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                "status" => true,
                'company' => [
                    'name' => 'the great enterprise'
                ]
            ]);

        $this->assertTrue($company->refresh()->isNotManager($member1));
    }

    public function testCanGetAllCompaniesWhenGuest()
    {
        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $companies = Company::factory()->count(20)
            ->create([
                "user_id" => $creator->id,
            ]);

        $query = "";
        $response = $this->getJson("/api/companies?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        $this->assertEquals(
            $data->meta->total,
            $companies->count()
        );
        $this->assertEquals(
            count($data->data),
            PaginationEnum::getUsers->value
        );

        if (is_null($data->links->next)) return;

        $response = $this->getJson($data->links->next);
        
        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        $this->assertEquals(
            count($data->data),
            PaginationEnum::getUsers->value
        );
    }

    public function testCanGetAllCompaniesWhenAdmin()
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
            "name" => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType);

        $companies = Company::factory()->count(20)
            ->create([
                "user_id" => $creator->id,
            ]);

        $this->actingAs($user);

        $query = "";
        $response = $this->getJson("/api/companies?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        $this->assertEquals(
            $data->meta->total,
            $companies->count()
        );
        $this->assertEquals(
            count($data->data),
            PaginationEnum::getUsers->value
        );

        if (is_null($data->links->next)) return;

        $response = $this->getJson($data->links->next);
        
        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        $this->assertEquals(
            count($data->data),
            PaginationEnum::getUsers->value
        );
    }

    public function testCanGetAllCompaniesWhenUser()
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
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);

        $companies = Company::factory()->count(20)
            ->create([
                "user_id" => $creator->id,
            ]);

        $this->actingAs($user);

        $query = "";
        $response = $this->getJson("/api/companies?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        $this->assertEquals(
            $data->meta->total,
            $companies->count()
        );
        $this->assertEquals(
            count($data->data),
            PaginationEnum::getUsers->value
        );

        if (is_null($data->links->next)) return;

        $response = $this->getJson($data->links->next);
        
        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        $this->assertEquals(
            count($data->data),
            PaginationEnum::getUsers->value
        );
    }

    public function testCanGetAllCompaniesWithNameQuery()
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
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);

        $companies = Company::factory()->count(20)
            ->create([
                "user_id" => $creator->id,
            ]);

        $this->actingAs($user);

        $name = "ac";
        $query = "name={$name}";
        $response = $this->getJson("/api/companies?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        
        $this->assertEquals(
            $data->meta->total,
            count(array_filter($companies->toArray(), function ($value) use ($name) {
                return str_contains($value["name"], $name);
            }))
        );
    }

    public function testCanGetAllCompaniesWithOwnerQuery()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator1 = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator2 = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);

        $creator2ProjectCount = 10;
        $companies = array_merge(Company::factory()->count(10)
            ->create([
                "user_id" => $creator1->id,
            ])->toArray(), Company::factory()->count($creator2ProjectCount)
            ->create([
                "user_id" => $creator2->id,
            ])->toArray());

        $this->actingAs($user);

        $query = "owner_id={$creator1->id}&owner_type=user";
        $response = $this->getJson("/api/companies?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        
        $this->assertEquals(
            $data->meta->total,
            $creator2ProjectCount
        );
    }

    public function testCanGetAllCompaniesWithMemberQuery()
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

        $member = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);

        $membershipCount = 3;
        $companies = Company::factory()->count(20)
            ->create([
                "user_id" => $creator->id,
            ]);

        for ($i=0; $i < $membershipCount; $i++) { 
            $relationship = $companies[$i]->addedByRelations()->create([
                "relationship_type" => RelationshipTypeEnum::companyMember->value
            ]);
            $relationship->to()->associate($member);
            $relationship->save();
        }

        $this->actingAs($user);

        $query = "member_id={$member->id}";
        $response = $this->getJson("/api/companies?". $query);
        
        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        
        $this->assertEquals(
            $data->meta->total,
            $membershipCount
        );
    }

    public function testCanGetAllCompaniesWithOfficialQuery()
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

        $official = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);

        $officialRelationshipCount = 3;
        $companies = Company::factory()->count(20)
            ->create([
                "user_id" => $creator->id,
            ]);

        for ($i=0; $i < $officialRelationshipCount; $i++) { 
            $relationship = $companies[$i]->addedByRelations()->create([
                "relationship_type" => RelationshipTypeEnum::companyAdministrator->value
            ]);
            $relationship->to()->associate($official);
            $relationship->save();
        }

        $this->actingAs($user);

        $query = "official_id={$official->id}";
        $response = $this->getJson("/api/companies?". $query);
        
        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        
        $this->assertEquals(
            $data->meta->total,
            $officialRelationshipCount
        );
    }

    public function testCanGetAllCompaniesWithMembershipQuery()
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

        $member = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);

        $membershipCount = 4;
        $companies = Company::factory()->count(20)
            ->create([
                "user_id" => $creator->id,
            ]);

        for ($i=0; $i < $membershipCount; $i++) { 
            $relationship = $companies[$i]->addedByRelations()->create([
                "relationship_type" => RelationshipTypeEnum::companyMember->value
            ]);
            $relationship->to()->associate($member);
            $relationship->save();
        }

        $this->actingAs($user);

        $query = "relationship_type=member";
        $response = $this->getJson("/api/companies?". $query);
        
        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        
        $this->assertEquals(
            $data->meta->total,
            $membershipCount
        );
    }

    public function testCanGetAllCompaniesWithOfficialRelationshipQuery()
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

        $member = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);

        $membershipCount = 4;
        $companies = Company::factory()->count(20)
            ->create([
                "user_id" => $creator->id,
            ]);

        for ($i=0; $i < $membershipCount; $i++) { 
            $relationship = $companies[$i]->addedByRelations()->create([
                "relationship_type" => RelationshipTypeEnum::companyAdministrator->value
            ]);
            $relationship->to()->associate($member);
            $relationship->save();
        }

        $this->actingAs($user);

        $query = "relationship_type=official";
        $response = $this->getJson("/api/companies?". $query);
        
        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        
        $this->assertEquals(
            $data->meta->total,
            $membershipCount
        );
    }

    public function testCanGetDetailsOfCompany()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $member = User::create([
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

        $companies = Company::factory()->count(5)->create([
            'name' => $this->faker->company(),
            'user_id' => $creator->id,
        ]);

        $sponsoredProject = Project::factory()->create([
            "addedby_id" => $user->id,
            "addedby_type" => $user::class,
        ]);

        $addedProject = Project::factory()->create([
            "addedby_id" => $companies[0]->id,
            "addedby_type" => $companies[0]::class,
        ]);

        $relationship = $companies[0]->addedByRelations()->create([
                "relationship_type" => RelationshipTypeEnum::companyMember->value
            ]);
        $relationship->to()->associate($member);
        $relationship->save();

        $participation = $sponsoredProject->participants()->create([
            "participating_as" => ProjectParticipantEnum::sponsor->value
        ]);
        $participation->participant()->associate($companies[0]);
        $participation->save();

        $response = $this->getJson("/api/companies/{$companies[0]->id}");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
            "company" => [
                "id" => $companies[0]->id,
                "owner" => [
                    "id" => $creator->id
                ],
                "members" => [
                    [
                        "id" => $relationship->id,
                        "member" => [
                            "id" => $member->id,
                            "name" => $member->name
                        ],
                    ],
                ],
                "projects" => [
                    [
                        "id" => $addedProject->id,
                        "name" => $addedProject->name
                    ],
                ],
                "sponsorships" => [
                    [
                        "id" => $sponsoredProject->id,
                        "name" => $sponsoredProject->name
                    ],
                ]
            ]
        ]);
    }

    public function testCannotGetMembersOfCompanyWithInvalidType()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $member = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $official = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $company = Company::create([
            'alias' => $this->faker->userName(),
            'name' => $this->faker->company(),
            'user_id' => $user->id,
        ]);
        
        $relationship = $company->addedByRelations()->create([
                "relationship_type" => RelationshipTypeEnum::companyMember->value
            ]);
        $relationship->to()->associate($member);
        $relationship->save();
        
        $relationship = $company->addedByRelations()->create([
                "relationship_type" => RelationshipTypeEnum::companyAdministrator->value
            ]);
        $relationship->to()->associate($official);
        $relationship->save();

        $response = $this->getJson("/api/companies/{$company->id}/wrong_type");

        $response->assertStatus(404);
        $response->assertJson([
            "status" => false,
            "message" => "this is an unknown request."
        ]);
    }

    public function testCanGetMembersOfCompany()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $member = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $official = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $company = Company::create([
            'alias' => $this->faker->userName(),
            'name' => $this->faker->company(),
            'user_id' => $user->id,
        ]);
        
        $relationship = $company->addedByRelations()->create([
                "relationship_type" => RelationshipTypeEnum::companyMember->value
            ]);
        $relationship->to()->associate($member);
        $relationship->save();
        
        $relationship = $company->addedByRelations()->create([
                "relationship_type" => RelationshipTypeEnum::companyAdministrator->value
            ]);
        $relationship->to()->associate($official);
        $relationship->save();

        $response = $this->getJson("/api/companies/{$company->id}/members");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
            "members" => [
                [
                    "relationshipType" => "member",
                    "member" => [
                        "name" => $member->name
                    ]
                ],
                [
                    "relationshipType" => "administrator",
                    "member" => [
                        "name" => $official->name
                    ]
                ],
            ]
        ]);
    }

    public function testCanGetOfficialsOfCompany()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $member = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $official = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $company = Company::create([
            'alias' => $this->faker->userName(),
            'name' => $this->faker->company(),
            'user_id' => $user->id,
        ]);
        
        $relationship = $company->addedByRelations()->create([
                "relationship_type" => RelationshipTypeEnum::companyMember->value
            ]);
        $relationship->to()->associate($member);
        $relationship->save();
        
        $relationship = $company->addedByRelations()->create([
                "relationship_type" => RelationshipTypeEnum::companyAdministrator->value
            ]);
        $relationship->to()->associate($official);
        $relationship->save();

        $response = $this->getJson("/api/companies/{$company->id}/officials");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
            "members" => [
                [
                    "relationshipType" => "administrator",
                    "member" => [
                        "name" => $official->name
                    ]
                ],
            ]
        ]);

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())->members),
            1
        );
    }

    public function testCannotGetCompanyProjectsWithInvalidType()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $company = Company::create([
            'alias' => $this->faker->userName(),
            'name' => $this->faker->company(),
            'user_id' => $user->id,
        ]);

        $project = Project::factory()->create([
            'addedby_type' => $company::class,
            'addedby_id' => $company->id,
        ]);

        $response = $this->getJson("/api/companies/{$company->id}/projects/wrong_type");

        $response->assertStatus(404);
        $response->assertJson([
            "status" => false,
            "message" => "this is an unknown request."
        ]);
    }

    public function testCanGetAllProjectsOfCompany()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $company = Company::create([
            'alias' => $this->faker->userName(),
            'name' => $this->faker->company(),
            'user_id' => $user->id,
        ]);

        $added = Project::factory()->create([
            'addedby_type' => $company::class,
            'addedby_id' => $company->id,
        ]);

        $sponsored = Project::factory()->create([
            'addedby_type' => $user::class,
            'addedby_id' => $user->id,
        ]);

        $participation = $sponsored->participants()->create([
            "participating_as" => ProjectParticipantEnum::sponsor->value
        ]);
        $participation->participant()->associate($company);
        $participation->save();

        $response = $this->getJson("/api/companies/{$company->id}/projects/all");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
            "projects" => [
                [
                    "id" => $added->id,
                    "name" => $added->name
                ],
                [
                    "id" => $sponsored->id,
                    "name" => $sponsored->name
                ],
            ]
        ]);
    }

    public function testCanGetSponsoredProjectsOfCompany()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $company = Company::create([
            'alias' => $this->faker->userName(),
            'name' => $this->faker->company(),
            'user_id' => $user->id,
        ]);

        $added = Project::factory()->create([
            'addedby_type' => $company::class,
            'addedby_id' => $company->id,
        ]);

        $sponsored = Project::factory()->create([
            'addedby_type' => $user::class,
            'addedby_id' => $user->id,
        ]);

        $participation = $sponsored->participants()->create([
            "participating_as" => ProjectParticipantEnum::sponsor->value
        ]);
        $participation->participant()->associate($company);
        $participation->save();

        $response = $this->getJson("/api/companies/{$company->id}/projects/sponsored");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
            "projects" => [
                [
                    "id" => $sponsored->id,
                    "name" => $sponsored->name
                ],
            ]
        ]);

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())->projects),
            1
        );
    }

    public function testCanGetAddedProjectsOfCompany()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $company = Company::create([
            'alias' => $this->faker->userName(),
            'name' => $this->faker->company(),
            'user_id' => $user->id,
        ]);

        $added = Project::factory()->create([
            'addedby_type' => $company::class,
            'addedby_id' => $company->id,
        ]);

        $sponsored = Project::factory()->create([
            'addedby_type' => $user::class,
            'addedby_id' => $user->id,
        ]);

        $participation = $sponsored->participants()->create([
            "participating_as" => ProjectParticipantEnum::sponsor->value
        ]);
        $participation->participant()->associate($company);
        $participation->save();

        $response = $this->getJson("/api/companies/{$company->id}/projects/added");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
            "projects" => [
                [
                    "id" => $added->id,
                    "name" => $added->name
                ],
            ]
        ]);

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())->projects),
            1
        );
    }
}
