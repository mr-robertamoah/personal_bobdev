<?php

namespace Tests\Feature;

use App\DTOs\CompanyDTO;
use App\Enums\RelationshipTypeEnum;
use App\Exceptions\CompanyException;
use App\Models\User;
use App\Models\UserType;
use App\Services\CompanyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

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

        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$manager->id => RelationshipTypeEnum::companyAdministrator->value],
            ])
        );

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

        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$other->id => 'administrator'],
            ])
        );

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

    public function testCanAddMultipleUsersAsMembersToCompanyWhenAnAdmin()
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

    public function testCanAddMultipleUsersAsMembersToCompanyWhenCompanyOwner()
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

    public function testCanAddMultipleUsersAsAdministratorsToCompanyWhenCompanyOwner()
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

    public function testCanAddMultipleUsersAsAdministratorsOrMembersToCompanyWhenCompanyOwnerOrAdmin()
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
    }

    public function testCanAddMultipleUsersAsMembersToCompanyWhenCompanyAdministrator()
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

        (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$companyAdmin->id => 'administrator'],
            ])
        );
        
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

        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$member1->id => 'member', $member2->id => 'member'],
            ])
        );
        
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

        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$member1->id => 'member', $member2->id => 'member'],
            ])
        );
        
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

        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$member1->id => 'administrator', $member2->id => 'administrator'],
            ])
        );
        
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

        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$member1->id => 'administrator', $member2->id => 'member'],
            ])
        );
        
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

        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$member1->id => 'administrator', $member2->id => 'member'],
            ])
        );
        
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

        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$member1->id => 'administrator', $member2->id => 'member'],
            ])
        );
        
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

        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$member1->id => 'administrator'],
            ])
        );
        
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

        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$member1->id => 'member'],
            ])
        );
        
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

        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$member1->id => 'administrator'],
            ])
        );
        
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
}
