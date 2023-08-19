<?php

namespace Tests\Unit;

use App\DTOs\CompanyDTO;
use App\DTOs\ProfileDTO;
use App\DTOs\ProjectDTO;
use App\Exceptions\ProfileException;
use App\Models\Profile;
use App\Models\Project;
use App\Models\User;
use App\Models\UserType;
use App\Services\CompanyService;
use App\Services\ProfileService;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateProfileWithoutProfileable()
    {
        $this->expectException(ProfileException::class);
        $this->expectExceptionMessage("Sorry! Cannot create profile because an owner was not found.");

        (new ProfileService)->createProfile(
            ProfileDTO::new()->fromArray([])
        );

        $this->assertTrue(true);
    }

    public function testCannotCreateProfileWithAWrongProfileableModel()
    {
        $this->expectException(ProfileException::class);
        $this->expectExceptionMessage("Sorry 😕! You can only create a profile for either a user or company.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')->create([

        ]);

        $profileable = (new ProjectService)->createProject(
                ProjectDTO::new()->fromArray([
                'addedby' => $user,
                'name' => 'project name',
                'description' => "the description"
            ])
        );

        (new ProfileService)->createProfile(
            ProfileDTO::new()->fromArray([
                'profileable' => $profileable
            ])
        );
    }

    public function testCannotCreateProfileWhenProfileableAlreadyHasOne()
    {
        $this->expectException(ProfileException::class);
        $this->expectExceptionMessage("Sorry 😏! This user or company already has a profile.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->has(Profile::factory(), 'profile')
            ->create();

        (new ProfileService)->createProfile(
            ProfileDTO::new()->fromArray([
                'profileable' => $user,
                'about' => 'this is what i want about me on my profile.'
            ])
        );
    }

    public function testUserCanCreateNewProfile()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $profile = (new ProfileService)->createProfile(
            ProfileDTO::new()->fromArray([
                'profileable' => $user,
                'about' => 'this is what i want about me on my profile.'
            ])
        );

        $this->assertEquals($user->id, $profile->profileable_id);
        $this->assertEquals($user::class, $profile->profileable::class);
    }

    public function testCompanyCanCreateNewProfile()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(26)->timestamp
            ]);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'The Great Enterprise',
                'alias' => 'the_great_ent',
                'userId' => $user->id
            ])
        );
        $profile = (new ProfileService)->createProfile(
            ProfileDTO::new()->fromArray([
                'profileable' => $company,
                'about' => 'this is what i want about me on my profile.'
            ])
        );

        $this->assertEquals($company->id, $profile->profileable_id);
        $this->assertEquals($company::class, $profile->profileable::class);
    }
    
    public function testCannotGetProfileWithoutId()
    {
        $this->expectException(ProfileException::class);
        $this->expectExceptionMessage("Sorry! Cannot get profile because an owner was not found.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $profileService = new ProfileService;

        $profile = $profileService->createProfile(
            ProfileDTO::new()->fromArray([
                'profileable' => $user,
                'about' => 'this is what i want about me on my profile.'
            ])
        );

        $this->assertTrue($profile->is($user->profile));

        $profileService->getProfile(
            ProfileDTO::new()->fromArray([
                'profileableId' => NUll,
                'profileableType' => 'user'
            ])
        );
    }
    
    public function testCannotGetProfileWithoutProfileType()
    {
        $this->expectException(ProfileException::class);
        $this->expectExceptionMessage("Sorry! Cannot get profile because an owner was not found.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(26)->timestamp
            ]);

        $profileService = new ProfileService;

        $profile = $profileService->createProfile(
            ProfileDTO::new()->fromArray([
                'profileable' => $user,
                'about' => 'this is what i want about me on my profile.'
            ])
        );

        $this->assertTrue($profile->is($user->profile));

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'The Great Enterprise',
                'alias' => 'the_great_ent',
                'userId' => $user->id
            ])
        );

        $profile = $profileService->createProfile(
            ProfileDTO::new()->fromArray([
                'profileable' => $company,
                'about' => 'this is what i want about me on my profile.'
            ])
        );

        $this->assertEquals($company->id, $profile->profileable_id);
        $this->assertEquals($company::class, $profile->profileable::class);

        $profileService->getProfile(
            ProfileDTO::new()->fromArray([
                'profileableId' => $company->id,
                'profileableType' => NULL
            ])
        );
    }

    public function testCanCreateAndGetProfileAsUserIfNoProfileExistsDuringGetting()
    {
        $user = User::factory()
            ->hasAttached(
                UserType::factory(state: [
                    'name' => UserType::FACILITATOR
                ]), [], 'userTypes'
            )
            ->create([
                'dob' => now()->subYears(26)->timestamp
            ]);

        $profile = (new ProfileService)->getProfile(
            ProfileDTO::new()->fromArray([
                'profileableId' => $user->id,
                'profileableType' => $user::class,
            ])
        );

        $this->assertTrue($user->profile->is($profile));
    }

    public function testCanCreateAndGetProfileAsCompanyIfNoProfileExistsDuringGetting()
    {
        $user = User::factory()
            ->hasAttached(
                UserType::factory(state: [
                    'name' => UserType::FACILITATOR
                ]), [], 'userTypes'
            )
            ->create([
                'dob' => now()->subYears(26)->timestamp
            ]);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'The Great Enterprise',
                'alias' => 'the_great_ent',
                'userId' => $user->id
            ])
        );

        $profile = (new ProfileService)->getProfile(
            ProfileDTO::new()->fromArray([
                'profileableId' => $company->id,
                'profileableType' => $company::class,
            ])
        );

        $this->assertTrue($company->profile->is($profile));
    }

    public function testCanGetProfileAsUserIfProfileExists()
    {
        $user = User::factory()
            ->hasAttached(
                UserType::factory(state: [
                    'name' => UserType::FACILITATOR
                ]), [], 'userTypes'
            )
            ->create([
                'dob' => now()->subYears(26)->timestamp
            ]);

        $profileService = (new ProfileService);

        $profileCreated = $profileService->createProfile(
            ProfileDTO::new()->fromArray([
                'profileableId' => $user->id,
                'profileableType' => $user::class,
            ])
        );

        $profileGotten = $profileService->getProfile(
            ProfileDTO::new()->fromArray([
                'profileableId' => $user->id,
                'profileableType' => $user::class,
            ])
        );

        $this->assertTrue($user->profile->is($profileGotten));
        $this->assertTrue($user->profile->is($profileCreated));
    }

    // tests for creating and getting company profile

    public function testCanGetProfileAsCompanyIfProfileExists()
    {
        $user = User::factory()
            ->hasAttached(
                UserType::factory(state: [
                    'name' => UserType::FACILITATOR
                ]), [], 'userTypes'
            )
            ->create([
                'dob' => now()->subYears(26)->timestamp
            ]);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                "name" => "A new company",
                "alias" => "new_company",
                "userId" => $user->id
            ])
        );

        $profileService = (new ProfileService);

        $profileCreated = $profileService->createProfile(
            ProfileDTO::new()->fromArray([
                'profileableId' => $company->id,
                'profileableType' => $company::class,
            ])
        );

        $profileGotten = $profileService->getProfile(
            ProfileDTO::new()->fromArray([
                'profileableId' => $company->id,
                'profileableType' => $company::class,
            ])
        );

        $this->assertTrue($company->profile->is($profileGotten));
        $this->assertTrue($company->profile->is($profileCreated));
    }
}
