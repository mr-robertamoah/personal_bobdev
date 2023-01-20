<?php

namespace Tests\Feature;

use App\DTOs\CompanyDTO;
use App\DTOs\ProjectDTO;
use App\DTOs\RequestDTO;
use App\Enums\ProjectParticipantEnum;
use App\Enums\RelationshipTypeEnum;
use App\Enums\RequestStateEnum;
use App\Exceptions\RequestException;
use App\Exceptions\ResponseException;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserType;
use App\Services\CompanyService;
use App\Services\ProjectService;
use App\Services\RequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RequestTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateAnyRequestWithoutForDetails()
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

        $to = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
        ]);

        $this->actingAs($user);

        $response = $this->postJson("api/request/create", [
            'toId' => $to->id,
            'toType' => 'user',
            'purpose' => 'member',
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => "Sorry, to make a request you need the request from someone to another person, and regarding something.",
                'exception' => RequestException::class
            ]);
    }

    public function testCannotCreateAnyRequestWithoutCorrectToDetails()
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

        $to = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
        ]);

        $for = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->actingAs($user);

        $response = $this->postJson("api/request/create", [
            'toId' => $to->id,
            'toType' => 'user',
            'forId' => $for->id + 1,
            'forType' => 'company',
            'purpose' => 'member',
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => "Sorry, to make a request you need the request from someone to another person, and regarding something.",
                'exception' => RequestException::class
            ]);
    }

    public function testCannotCreateAnyRequestWithoutPurpose()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $to = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $for = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->actingAs($user);

        $response = $this->postJson("api/request/create", [
            'toId' => $to->id,
            'toType' => 'user',
            'forId' => $for->id,
            'forType' => 'company',
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => "The purpose field is required.",
                'errors' => [
                    'purpose' => [
                        'The purpose field is required.'
                    ]
                ]
            ]);
    }

    public function testCannotCreateCompanyRequestWithWrongPurpose()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $to = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $for = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->actingAs($user);

        $response = $this->postJson("api/request/create", [
            'toId' => $to->id,
            'toType' => 'user',
            'forId' => $for->id,
            'forType' => 'company',
            'purpose' => 'hey'
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => "Sorry, hey is not a valid purpose for companies.",
                'exception' => RequestException::class
            ]);
    }

    public function testCannotCreateProjectRequestWithWrongPurpose()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $to = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $user,
                'name' => 'PHP development project',
                'description' => 'this is the description of the project'
            ])
        );

        $this->actingAs($user);

        $response = $this->postJson("api/request/create", [
            'toId' => $to->id,
            'toType' => 'user',
            'forId' => $for->id,
            'forType' => 'project',
            'purpose' => 'hey'
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => "Sorry, hey is not a valid purpose for projects.",
                'exception' => RequestException::class
            ]);
    }

    public function testCanCreateProjectRequestWithLearnerOrFacilitatorPurposeAsOfficialToUserWithRightUserType()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $user->userTypes()->attach($userType->id);

        $to = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $to->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $to->userTypes()->attach($userType->id);

        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $user,
                'name' => 'PHP development project',
                'description' => 'this is the description of the project'
            ])
        );

        $this->actingAs($user);

        $response = $this->postJson("api/request/create", [
            'toId' => $to->id,
            'toType' => 'user',
            'forId' => $for->id,
            'forType' => 'project',
            'purpose' => 'facilitator'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'request' => [
                    'to' => [
                        'id' => $to->id,
                        'name' => $to->name,
                    ],
                    'from' => [
                        'id' => $user->id,
                        'name' => $user->name,
                    ],
                    'for' => [
                        'id' => $for->id,
                        'name' => $for->name,
                        'description' => $for->description,
                    ],
                    'purpose' => ProjectParticipantEnum::facilitator->value
                ]
            ]);
    }

    public function testCanCreateProjectRequestWithLearnerOrFacilitatorPurposeAsUserWithRightUserTypeToOfficial()
    {
        $official = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $official->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $official->userTypes()->attach($userType->id);

        $from = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $from->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $from->userTypes()->attach($userType->id);

        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $official,
                'name' => 'PHP development project',
                'description' => 'this is the description of the project'
            ])
        );

        $this->actingAs($from);

        $response = $this->postJson("api/request/create", [
            'toId' => $official->id,
            'toType' => 'user',
            'forId' => $for->id,
            'forType' => 'project',
            'purpose' => 'facilitator'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'request' => [
                    'to' => [
                        'id' => $official->id,
                        'name' => $official->name,
                    ],
                    'from' => [
                        'id' => $from->id,
                        'name' => $from->name,
                    ],
                    'for' => [
                        'id' => $for->id,
                        'name' => $for->name,
                        'description' => $for->description,
                    ],
                    'purpose' => ProjectParticipantEnum::facilitator->value
                ]
            ]);
    }

    public function testCanCreateProjectRequestWithSponsorPurposeAsOfficialToUserWithDonorUserType()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $user->userTypes()->attach($userType->id);

        $to = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $to->addedUserTypes()->create([
            'name' => UserType::DONOR
        ]);

        $to->userTypes()->attach($userType->id);

        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $user,
                'name' => 'PHP development project',
                'description' => 'this is the description of the project'
            ])
        );

        $this->actingAs($user);

        $response = $this->postJson("api/request/create", [
            'toId' => $to->id,
            'toType' => 'user',
            'forId' => $for->id,
            'forType' => 'project',
            'purpose' => 'sponsor'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'request' => [
                    'to' => [
                        'id' => $to->id,
                        'name' => $to->name,
                    ],
                    'from' => [
                        'id' => $user->id,
                        'name' => $user->name,
                    ],
                    'for' => [
                        'id' => $for->id,
                        'name' => $for->name,
                        'description' => $for->description,
                    ],
                    'purpose' => ProjectParticipantEnum::sponsor->value
                ]
            ]);
    }

    public function testCanCreateProjectRequestWithSponsorPurposeAsCompanyToOfficial()
    {
        $official = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $official->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $official->userTypes()->attach($userType->id);

        $from = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $from->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $from->userTypes()->attach($userType->id);

        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $official,
                'name' => 'PHP development project',
                'description' => 'this is the description of the project'
            ])
        );

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $from,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise'
            ])
        );

        $this->actingAs($from);

        $response = $this->postJson("api/request/create", [
            'fromId' => $company->id,
            'fromType' => 'company',
            'forId' => $for->id,
            'forType' => 'project',
            'purpose' => 'sponsor'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'request' => [
                    'to' => [
                        'id' => $official->id,
                        'name' => $official->name,
                    ],
                    'from' => [
                        'id' => $company->id,
                        'name' => $company->name,
                    ],
                    'for' => [
                        'id' => $for->id,
                        'name' => $for->name,
                        'description' => $for->description,
                    ],
                    'purpose' => ProjectParticipantEnum::sponsor->value
                ]
            ]);
    }

    public function testCanCreateCompanyRequestWithMemberPurposeAsUserToOfficial()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $user->userTypes()->attach($userType->id);

        $official = User::create([
            'username' => "mr_kwameamoah",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_kwameamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $official->userTypes()->attach($userType->id);

        $from = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $from->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $from->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'about' => 'this is the description of the project'
            ])
        );
        
        (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'company' => $company,
                'memberships' => [$official->id => RelationshipTypeEnum::companyAdministrator->value],
            ])
        );

        $this->actingAs($from);

        $response = $this->postJson("api/request/create", [
            'toId' => $official->id,
            'toType' => 'user',
            'forId' => $company->id,
            'forType' => 'company',
            'purpose' => 'member'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'request' => [
                    'to' => [
                        'id' => $official->id,
                        'name' => $official->name,
                    ],
                    'from' => [
                        'id' => $from->id,
                        'name' => $from->name,
                    ],
                    'for' => [
                        'id' => $company->id,
                        'name' => $company->name,
                        'about' => $company->about,
                    ],
                    'purpose' => "MEMBER"
                ]
            ]);
    }

    public function testCanCreateCompanyRequestWithMemberPurposeAsOfficialToUser()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $user->userTypes()->attach($userType->id);

        $official = User::create([
            'username' => "mr_kwameamoah",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_kwameamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $official->userTypes()->attach($userType->id);

        $potentialMember = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $potentialMember->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $potentialMember->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'about' => 'this is the description of the project'
            ])
        );
        
        (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'company' => $company,
                'memberships' => [$official->id => RelationshipTypeEnum::companyAdministrator->value],
            ])
        );

        $this->actingAs($official);

        $response = $this->postJson("api/request/create", [
            'toId' => $potentialMember->id,
            'toType' => 'user',
            'forId' => $company->id,
            'forType' => 'company',
            'purpose' => 'member'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'request' => [
                    'to' => [
                        'id' => $potentialMember->id,
                        'name' => $potentialMember->name,
                    ],
                    'from' => [
                        'id' => $official->id,
                        'name' => $official->name,
                    ],
                    'for' => [
                        'id' => $company->id,
                        'name' => $company->name,
                        'about' => $company->about,
                    ],
                    'purpose' => "MEMBER"
                ]
            ]);
    }

    public function testCanCreateCompanyRequestWithAdministratorPurposeAsOwnerToUser()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $user->userTypes()->attach($userType->id);

        $potentialAdministrator = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $potentialAdministrator->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $potentialAdministrator->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'about' => 'this is the description of the project'
            ])
        );

        $this->actingAs($user);

        $response = $this->postJson("api/request/create", [
            'toId' => $potentialAdministrator->id,
            'toType' => 'user',
            'forId' => $company->id,
            'forType' => 'company',
            'purpose' => 'member'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'request' => [
                    'to' => [
                        'id' => $potentialAdministrator->id,
                        'name' => $potentialAdministrator->name,
                    ],
                    'from' => [
                        'id' => $user->id,
                        'name' => $user->name,
                    ],
                    'for' => [
                        'id' => $company->id,
                        'name' => $company->name,
                        'about' => $company->about,
                    ],
                    'purpose' => "MEMBER"
                ]
            ]);
    }

    public function testCanCreateCompanyRequestWithAdministratorPurposeAsUserToOwner()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $user->userTypes()->attach($userType->id);

        $potentialAdministrator = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $potentialAdministrator->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $potentialAdministrator->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'about' => 'this is the description of the project'
            ])
        );

        $this->actingAs($user);

        $response = $this->postJson("api/request/create", [
            'toId' => $potentialAdministrator->id,
            'toType' => 'user',
            'forId' => $company->id,
            'forType' => 'company',
            'purpose' => 'member'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'request' => [
                    'to' => [
                        'id' => $potentialAdministrator->id,
                        'name' => $potentialAdministrator->name,
                    ],
                    'from' => [
                        'id' => $user->id,
                        'name' => $user->name,
                    ],
                    'for' => [
                        'id' => $company->id,
                        'name' => $company->name,
                        'about' => $company->about,
                    ],
                    'purpose' => "MEMBER"
                ]
            ]);
    }

    public function testCannotRespondWithoutResponse()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $user->userTypes()->attach($userType->id);

        $potentialAdministrator = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $potentialAdministrator->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $potentialAdministrator->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'about' => 'this is the description of the project'
            ])
        );

        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $user,
                'to' => $potentialAdministrator,
                'for' => $company,
                'purpose' => RelationshipTypeEnum::companyAdministrator->value,
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'to_type' => $potentialAdministrator::class,
            'to_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'purpose' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->actingAs($user);

        $response = $this->postJson("api/request/{$request->id}/update", [
            
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => "The response field is required.",
                'errors' => [
                    'response' => [
                        "The response field is required."
                    ],
                ]
            ]);
    }

    public function testCannotRespondWithInvalidResponse()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $user->userTypes()->attach($userType->id);

        $potentialAdministrator = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $potentialAdministrator->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $potentialAdministrator->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'about' => 'this is the description of the project'
            ])
        );

        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $user,
                'to' => $potentialAdministrator,
                'for' => $company,
                'purpose' => RelationshipTypeEnum::companyAdministrator->value,
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'to_type' => $potentialAdministrator::class,
            'to_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'purpose' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->actingAs($user);

        $response = $this->postJson("api/request/{$request->id}/update", [
            'response' => $requestResponse = "solved"
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => "Sorry! {$requestResponse} is not a valid response for a request.",
                'exception' => ResponseException::class
            ]);
    }

    public function testCannotRespondToInvalidRequest()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $user->userTypes()->attach($userType->id);

        $potentialAdministrator = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $potentialAdministrator->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $potentialAdministrator->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'about' => 'this is the description of the project'
            ])
        );

        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $user,
                'to' => $potentialAdministrator,
                'for' => $company,
                'purpose' => RelationshipTypeEnum::companyAdministrator->value,
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'to_type' => $potentialAdministrator::class,
            'to_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'purpose' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->actingAs($user);

        $response = $this->postJson("api/request/10/update", [
            'response' => $requestResponse = "solved"
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => "Sorry! You need a request to respond to. No request was found.",
                'exception' => ResponseException::class
            ]);
    }

    public function testCanRespondToRequestWhenRecepient()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $user->userTypes()->attach($userType->id);

        $potentialAdministrator = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $potentialAdministrator->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $potentialAdministrator->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'about' => 'this is the description of the project'
            ])
        );

        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $user,
                'to' => $potentialAdministrator,
                'for' => $company,
                'purpose' => RelationshipTypeEnum::companyAdministrator->value,
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'to_type' => $potentialAdministrator::class,
            'to_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'purpose' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->actingAs($potentialAdministrator);

        $response = $this->postJson("api/request/{$request->id}/update", [
            'response' => "accepted"
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'request' => [
                    'id' => $request->id,
                    'state' => RequestStateEnum::accepted->value,
                    'purpose' => RelationshipTypeEnum::companyAdministrator->value,
                ]
            ]);
    }

    public function testCanRespondToRequestWhenNotRecepientButOwnerOfCompanyAndRecepientBeingOfficial()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $user->userTypes()->attach($userType->id);

        $potentialMember = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $potentialMember->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $potentialMember->userTypes()->attach($userType->id);

        $official = User::create([
            'username' => "kofiamoah1",
            'first_name' => "Kofi",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kofiamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $official->userTypes()->attach($userType->id);

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'about' => 'this is the description of the project'
            ])
        );

        (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'company' => $company,
                'memberships' => [$official->id => RelationshipTypeEnum::companyAdministrator->value]
            ])
        );

        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $potentialMember,
                'to' => $official,
                'for' => $company,
                'purpose' => RelationshipTypeEnum::companyMember->value,
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $potentialMember::class,
            'from_id' => $potentialMember->id,
            'to_type' => $official::class,
            'to_id' => $official->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'purpose' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->actingAs($user);

        $response = $this->postJson("api/request/{$request->id}/update", [
            'response' => "accepted"
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'request' => [
                    'id' => $request->id,
                    'state' => RequestStateEnum::accepted->value,
                    'purpose' => RelationshipTypeEnum::companyMember->value,
                ]
            ]);
    }

    public function testCanRespondToRequestWhenNotRecepientButAdmin()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $user->userTypes()->attach($userType->id);

        $potentialMember = User::create([
            'username' => "kwameamoah1",
            'first_name' => "Kwame",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kwameamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
        ]);

        $userType = $potentialMember->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $potentialMember->userTypes()->attach($userType->id);

        $admin = User::create([
            'username' => "kofiamoah1",
            'first_name' => "Kofi",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "kofiamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString(),
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
                'about' => 'this is the description of the project'
            ])
        );

        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $potentialMember,
                'to' => $user,
                'for' => $company,
                'purpose' => RelationshipTypeEnum::companyMember->value,
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $potentialMember::class,
            'from_id' => $potentialMember->id,
            'to_type' => $user::class,
            'to_id' => $user->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'purpose' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->actingAs($admin);

        $response = $this->postJson("api/request/{$request->id}/update", [
            'response' => "accepted"
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'request' => [
                    'id' => $request->id,
                    'state' => RequestStateEnum::accepted->value,
                    'purpose' => RelationshipTypeEnum::companyMember->value,
                ]
            ]);
    }
}
