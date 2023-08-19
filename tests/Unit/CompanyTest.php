<?php

namespace Tests\Unit;

use App\DTOs\CompanyDTO;
use App\Enums\RelationshipTypeEnum;
use App\Exceptions\CompanyException;
use App\Exceptions\UserException;
use App\Models\User;
use App\Models\UserType;
use App\Services\CompanyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateCompanyIfUserHasNoDob()
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage("Sorry! User, with name Amoah Robert, has not yet specified date of birth.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'The great enterprise'
            ])
        );
    }

    public function testCannotCreateCompanyIfNotAnAdult()
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage("Sorry! User, with name Amoah Robert, is not yet an adult.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'The great enterprise'
            ])
        );
    }

    public function testCannotCreateCompanyWithoutUser()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! There must be a user for this action to be performed.");

        (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'name' => 'The great enterprise'
            ])
        );
    }

    public function testCannotCreateCompanyWithNonAdminUserAndOwner()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! You must be an administrator to be able to create a company on behalf of another user.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise'
            ])
        );
    }

    public function testCanCreateCompanyWithAnAdminUserAndOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
    }

    public function testCannotCreateCompanyWithAliasLessThanEightCharaters()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! The company alias provided must have at least 8 characters.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenter'
            ])
        );

        $this->assertDatabaseMissing('companies', [
            'name' => 'The great enterprise',
            'alias' => 'tgenter',
            'user_id' => $user->id
        ]);
    }

    public function testCanCreateCompanyAsAnAdultUser()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'alias' => 'tgenterprise',
            'user_id' => $user->id
        ]);
    }

    public function testCannotUpdateCompanyWithoutProvidingCompanyId()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! You need to provide a company to be able to perform this action.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->updateCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'name' => 'The great ent',
            ])
        );

        $this->assertDatabaseMissing('companies', [
            'name' => 'The great ent',
            'user_id' => $owner->id
        ]);
    }

    public function testCannotUpdateCompanyWithoutBeingAnAdminUserOrOwner()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to perform this action on company with name The great enterprise.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->updateCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $other->id,
                'companyId' => $company->id,
                'name' => 'The great ent',
            ])
        );

        $this->assertDatabaseMissing('companies', [
            'name' => 'The great ent',
            'user_id' => $owner->id
        ]);
    }

    public function testCannotUpdateCompanyWithoutRightData()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! There is not enough data to update the information the company with name The great enterprise.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->updateCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'companyId' => $company->id,
            ])
        );
    }

    public function testCanUpdateCompanyIfAnAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->updateCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'companyId' => $company->id,
                'name' => 'The great ent',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great ent',
            'user_id' => $owner->id
        ]);

        $this->assertDatabaseMissing('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
    }

    public function testCanUpdateCompanyIfOwner()
    {
        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->updateCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'companyId' => $company->id,
                'name' => 'The great ent',
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great ent',
            'user_id' => $owner->id
        ]);

        $this->assertDatabaseMissing('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $owner::class,
            'performedby_id' => $owner->id,
            'performedon_type' => $company::class,
            'performedon_id' => $company->id,
            'action' => 'update'
        ]);
    }

    public function testCanUpdateCompanyIfManager()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $manager = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Clair',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($manager);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $manager::class,
            'to_id' => $manager->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
            
        $status = (new CompanyService)->updateCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $manager->id,
                'companyId' => $company->id,
                'name' => 'The great ent.'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great ent.',
            'user_id' => $owner->id
        ]);

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $manager::class,
            'performedby_id' => $manager->id,
            'performedon_type' => $company::class,
            'performedon_id' => $company->id,
            'action' => 'update'
        ]);
    }

    public function testCannotDeleteCompanyWithoutProvidingCompanyId()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! You need to provide a company to be able to perform this action.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->deleteCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
    }

    public function testCannotDeleteCompanyWithoutBeingAnAdminUserOrOwner()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to perform this action on company with name The great enterprise.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->deleteCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $other->id,
                'companyId' => $company->id,
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
    }

    public function testCanDeleteCompanyIfAnAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        $status = (new CompanyService)->deleteCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'companyId' => $company->id,
            ])
        );

        $this->assertSoftDeleted('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
    }

    public function testCanDeleteCompanyIfOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        $status = (new CompanyService)->deleteCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'companyId' => $company->id,
            ])
        );

        $this->assertSoftDeleted('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
    }

    public function testCannotDeleteCompanyIfManager()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to perform this action on company with name The great enterprise.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $manager = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Clair',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($manager);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $manager::class,
            'to_id' => $manager->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
            
        $status = (new CompanyService)->deleteCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $manager->id,
                'companyId' => $company->id,
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
    }

    public function testCannotSendMembershipRequestWithoutUser()
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage("Sorry! User is required.");

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'companyId' => $company->id,
            ])
        );
    }

    public function testUserCannotSendMembershipRequestToCompanyWithoutProvidingCompanyId()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! You need to provide a company to be able to perform this action.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
            ])
        );
    }

    public function testCannotSendMembershipRequestFromCompanyWithoutBeingAdminOrCompanyOfficial()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to perform this action on company with name The great enterprise.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $other->id,
                'companyId' => $company->id,
            ])
        );
    }

    public function testCannotSendMembershipRequestToPotentialAdministratorOfCompanyWithoutBeingAdminOrCompanyOwner()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to perform this action on company with name The great enterprise.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);

        $member = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwesi',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member::class,
            'to_id' => $member->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $member->id,
                'companyId' => $company->id,
                'relationshipType' => RelationshipTypeEnum::companyMember->value,
                'memberships' => [$other->id]
            ])
        );
    }

    public function testCannotSendMembershipRequestFromCompanyWithEmptyMembershipList()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage('Sorry! The users and their respective membership type must be specified.');

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'companyId' => $company->id,
                'memberships' => []
            ])
        );

        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => 10,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCannotSendMembershipRequestFromCompanyWithMembershipListOfIdsWithoutMainRelationshipType()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage('Sorry! You need to provide a list of user ids pointing to the membership type you wish to establish with the company.');

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'companyId' => $company->id,
                'memberships' => [10, 2]
            ])
        );

        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => 10,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCannotSendMembershipRequestForCompanyWithNonIdKeysForMembershipArray()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage('Sorry! The user ids must point to respective membership types.');

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'companyId' => $company->id,
                'memberships' => ['member' => $other->id]
            ])
        );

        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => 10,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCannotSendMembershipRequestToInvalidUserForCompanyIfAnAdminOrCompanyOwner()
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage("Sorry! User was not found.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'companyId' => $company->id,
                'memberships' => [10 => 'member']
            ])
        );

        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => 10,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCannotSendMembershipRequestAsMemberOfCompanyWithoutSpecifyingRelationshipType()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! the relationship type you wish to establish must be specified for user with name Amoah Kwame.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'companyId' => $company->id,
                'memberships' => [$other->id => '']
            ])
        );

        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
        ]);
    }

    public function testCannotSendMembershipRequestToANonAdultUserToBeAdministratorOfCompanyIfCompanyOwner()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! Amoah Kwame must be an adult in order to have such a relationship with a company.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'companyId' => $company->id,
                'memberships' => [$other->id => 'administrator']
            ])
        );

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
    }

    public function testCanSendMembershipRequestToANonAdultUserToBeMemberOfCompanyIfCompanyOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'companyId' => $company->id,
                'memberships' => [$other->id => 'member']
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $owner::class,
            'from_id' => $owner->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCanSendMembershipRequestToPotentialAdministratorOfCompanyIfAnAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'companyId' => $company->id,
                'memberships' => [$other->id => 'administrator']
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
    }

    public function testCanSendMembershipRequestToPotentialMemberOfCompanyIfAnAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'companyId' => $company->id,
                'memberships' => [$other->id => 'member']
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCanSendMembershipRequestToPotentialAdministratorOfCompanyIfCompanyOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'companyId' => $company->id,
                'memberships' => [$other->id => 'administrator']
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $owner::class,
            'from_id' => $owner->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
    }

    public function testCanSendMembershipRequestToPotentialMemberOfCompanyIfCompanyOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'companyId' => $company->id,
                'memberships' => [$other->id => 'member']
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $owner::class,
            'from_id' => $owner->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCanSendMembershipRequestToPotentialMemberForCompanyWhenCompanyAdministrator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);

        $companyAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwesi',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
        
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($companyAdministrator);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $companyAdministrator::class,
            'to_id' => $companyAdministrator->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->assertTrue($company->refresh()->isOfficial($companyAdministrator));
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $companyAdministrator->id,
                'companyId' => $company->id,
                'memberships' => [$other->id => 'member']
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $companyAdministrator::class,
            'from_id' => $companyAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCanSendMultipleMembershipRequestsWithDifferentRelationshipTypesForCompanyIfAuthorized()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $otherMember1 = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);

        $otherMember2 = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->sendMembershipRequest(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'companyId' => $company->id,
                'memberships' => [$otherMember1->id => 'member', $otherMember2->id => 'administrator']
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $otherMember1::class,
            'to_id' => $otherMember1->id,
            'type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $otherMember2::class,
            'to_id' => $otherMember2->id,
            'type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $otherMember1::class,
            'to_id' => $otherMember1->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $otherMember2::class,
            'to_id' => $otherMember2->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
    }

    public function testCannotRemoveMemberWhoIsAnInvalidUserFromCompanyIfAnAdminOrCompanyOwner()
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage("Sorry! User was not found.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($other);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
            
        (new CompanyService)->removeMembers(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'companyId' => $company->id,
                'memberships' => [10 => 'member']
            ])
        );

        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $other::class,
            'to_id' => $other->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
    }

    public function testCannotRemoveANonMemberFromCompanyIfAnAdminOrCompanyOwner()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! Atta Ofori must be a member of The great enterprise company.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $member = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);

        $nonMember = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Ofori',
                'surname' => 'Atta'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member::class,
            'to_id' => $member->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
            
        (new CompanyService)->removeMembers(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'companyId' => $company->id,
                'memberships' => [$nonMember->id => 'member']
            ])
        );
    }

    public function testCannotRemoveAnAdministratorFromCompanyIfAnAdministratorOfCompany()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to perform this action on the company with name The great enterprise");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $administrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);

        $member = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Ofori',
                'surname' => 'Atta'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($administrator);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $administrator::class,
            'to_id' => $administrator->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($member);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member::class,
            'to_id' => $member->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->assertTrue($company->refresh()->isOfficial($member));
            
        (new CompanyService)->removeMembers(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $administrator->id,
                'companyId' => $company->id,
                'memberships' => [$member->id => 'administrator']
            ])
        );
    }

    public function testCannotRemoveANonMemberIfAdministratorOfCompany()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! Atta Ofori must be a member of The great enterprise company.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $administrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);

        $member = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Ofori',
                'surname' => 'Atta'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($administrator);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $administrator::class,
            'to_id' => $administrator->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->assertDatabaseMissing('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member::class,
            'to_id' => $member->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->assertFalse($company->refresh()->isMember($member));
            
        (new CompanyService)->removeMembers(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $administrator->id,
                'companyId' => $company->id,
                'memberships' => [$member->id => 'member']
            ])
        );
    }

    public function testCanRemoveAUserAsMemberFromCompanyIfAnAdministratorOfCompany()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $administrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);

        $member = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Ofori',
                'surname' => 'Atta'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member);
        $relation->save();

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($administrator);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $administrator::class,
            'to_id' => $administrator->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member::class,
            'to_id' => $member->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->assertTrue($company->refresh()->isMember($member));
            
        (new CompanyService)->removeMembers(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $administrator->id,
                'companyId' => $company->id,
                'memberships' => [$member->id => 'member']
            ])
        );

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $administrator::class,
            'performedby_id' => $administrator->id,
            'performedon_type' => $company::class,
            'performedon_id' => $company->id,
            'action' => "removeMember",
        ]);
    }

    public function testCanRemoveAUserAsMemberFromCompanyIfAnAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $administrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);

        $member = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Ofori',
                'surname' => 'Atta'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($administrator);
        $relation->save();

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $administrator::class,
            'to_id' => $administrator->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member::class,
            'to_id' => $member->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->assertTrue($company->refresh()->isMember($member));
            
        (new CompanyService)->removeMembers(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'companyId' => $company->id,
                'memberships' => [$member->id => 'member', $administrator->id => 'administrator']
            ])
        );

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $user::class,
            'performedby_id' => $user->id,
            'performedon_type' => $company::class,
            'performedon_id' => $company->id,
            'action' => "removeMember",
        ]);

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $user::class,
            'performedby_id' => $user->id,
            'performedon_type' => $company::class,
            'performedon_id' => $company->id,
            'action' => "removeMember",
        ]);
    }

    public function testCanRemoveAUserAsMemberFromCompanyIfCompanyOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $administrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);

        $member = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Ofori',
                'surname' => 'Atta'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($administrator);
        $relation->save();

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member);
        $relation->save();
        
        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $administrator::class,
            'to_id' => $administrator->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member::class,
            'to_id' => $member->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->assertTrue($company->refresh()->isMember($member));
            
        (new CompanyService)->removeMembers(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'companyId' => $company->id,
                'memberships' => [$member->id => 'member', $administrator->id => 'administrator']
            ])
        );

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $owner::class,
            'performedby_id' => $owner->id,
            'performedon_type' => $company::class,
            'performedon_id' => $company->id,
            'action' => "removeMember",
        ]);

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $owner::class,
            'performedby_id' => $owner->id,
            'performedon_type' => $company::class,
            'performedon_id' => $company->id,
            'action' => "removeMember",
        ]);
    }

    public function testCannotLeaveCompanyIfOwner()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! Amoah Robert is the owner and is not a member.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->leave(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $owner->id,
                'companyId' => $company->id,
                'relationshipType' => 'member'
            ])
        );

        $this->assertDatabaseMissing('activities', [
            'performedby_type' => $owner::class,
            'performedby_id' => $owner->id,
            'performedon_type' => $company::class,
            'performedon_id' => $company->id,
            'action' => "removeMember",
        ]);
    }

    public function testCannotLeaveCompanyIfNotMemberOrAdministratorOfCompany()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! Amoah Kwame must be a member of The great enterprise company.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Kwame',
                'surname' => 'Amoah'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        (new CompanyService)->leave(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $other->id,
                'companyId' => $company->id,
                'relationshipType' => 'member'
            ])
        );

        $this->assertDatabaseMissing('activities', [
            'performedby_type' => $owner::class,
            'performedby_id' => $owner->id,
            'performedon_type' => $company::class,
            'performedon_id' => $company->id,
            'action' => "removeMember",
        ]);
    }

    public function testCannotLeaveCompanyAsAdministratorIfMember()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! Atta Ofori is not a administrator of The great enterprise company.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $member = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Ofori',
                'surname' => 'Atta'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member::class,
            'to_id' => $member->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->assertTrue($company->refresh()->isMember($member));
            
        (new CompanyService)->leave(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $member->id,
                'companyId' => $company->id,
                'relationshipType' => 'administrator'
            ])
        );

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $member::class,
            'performedby_id' => $member->id,
            'performedon_type' => $company::class,
            'performedon_id' => $company->id,
            'action' => "removeMember",
        ]);
    }

    public function testCannotLeaveCompanyAsMemberIfAdministrator()
    {
        $this->expectException(CompanyException::class);
        $this->expectExceptionMessage("Sorry! Atta Ofori is not a member of The great enterprise company.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $member = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Ofori',
                'surname' => 'Atta'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
    
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($member);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member::class,
            'to_id' => $member->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->assertTrue($company->refresh()->isManager($member));
            
        (new CompanyService)->leave(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $member->id,
                'companyId' => $company->id,
                'relationshipType' => 'member'
            ])
        );

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $member::class,
            'performedby_id' => $member->id,
            'performedon_type' => $company::class,
            'performedon_id' => $company->id,
            'action' => "removeMember",
        ]);
    }

    public function testCanLeaveCompanyWhenAMember()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $member = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Ofori',
                'surname' => 'Atta'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member::class,
            'to_id' => $member->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->assertTrue($company->refresh()->isMember($member));
            
        (new CompanyService)->leave(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $member->id,
                'companyId' => $company->id,
                'relationshipType' => 'member'
            ])
        );

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $member::class,
            'performedby_id' => $member->id,
            'performedon_type' => $company::class,
            'performedon_id' => $company->id,
            'action' => "removeMember",
        ]);
    }

    public function testCanLeaveIfCompanyAdministrator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(10)->toDateTimeString(),
                'first_name' => 'Kojo',
                'surname' => 'Amoah'
            ]);

        $owner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Robert',
                'surname' => 'Amoah'
            ]);

        $member = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString(),
                'first_name' => 'Ofori',
                'surname' => 'Atta'
            ]);
            
        $company = (new CompanyService)->createCompany(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $user->id,
                'ownerId' => $owner->id,
                'name' => 'The great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->assertDatabaseHas('companies', [
            'name' => 'The great enterprise',
            'user_id' => $owner->id
        ]);
            
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($member);
        $relation->save();

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $member::class,
            'to_id' => $member->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->assertTrue($company->refresh()->isManager($member));
            
        (new CompanyService)->leave(
            companyDTO: CompanyDTO::new()->fromArray([
                'userId' => $member->id,
                'companyId' => $company->id,
                'relationshipType' => 'administrator'
            ])
        );

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $member::class,
            'performedby_id' => $member->id,
            'performedon_type' => $company::class,
            'performedon_id' => $company->id,
            'action' => "removeMember",
        ]);
    }
}
