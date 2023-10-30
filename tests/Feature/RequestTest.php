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
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use App\Models\UserType;
use App\Services\CompanyService;
use App\Services\ProjectService;
use App\Services\RequestService;
use App\Traits\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RequestTest extends TestCase
{
    use RefreshDatabase;
    use TestTrait;
    use WithFaker;

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

        $response = $this->postJson("api/request", [
            'toId' => $to->id,
            'toType' => 'user',
            'type' => 'member',
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

        $response = $this->postJson("api/request", [
            'toId' => $to->id,
            'toType' => 'user',
            'forId' => $for->id + 1,
            'forType' => 'company',
            'type' => 'member',
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => "Sorry, to make a request you need the request from someone to another person, and regarding something.",
                'exception' => RequestException::class
            ]);
    }

    public function testCannotCreateAnyRequestWithoutType()
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

        $response = $this->postJson("api/request", [
            'toId' => $to->id,
            'toType' => 'user',
            'forId' => $for->id,
            'forType' => 'company',
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => "The type field is required.",
                'errors' => [
                    'type' => [
                        'The type field is required.'
                    ]
                ]
            ]);
    }

    public function testCannotCreateCompanyRequestWithWrongType()
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

        $response = $this->postJson("api/request", [
            'toId' => $to->id,
            'toType' => 'user',
            'forId' => $for->id,
            'forType' => 'company',
            'type' => 'hey'
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => "Sorry, hey is not a valid type for companies.",
                'exception' => RequestException::class
            ]);
    }

    public function testCannotCreateProjectRequestWithWrongType()
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

        $response = $this->postJson("api/request", [
            'toId' => $to->id,
            'toType' => 'user',
            'forId' => $for->id,
            'forType' => 'project',
            'type' => 'hey'
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => "Sorry, hey is not a valid type for projects.",
                'exception' => RequestException::class
            ]);
    }

    public function testCanCreateProjectRequestWithLearnerOrFacilitatorTypeAsOfficialToUserWithRightUserType()
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

        $response = $this->postJson("api/request", [
            'toId' => $to->id,
            'toType' => 'user',
            'forId' => $for->id,
            'forType' => 'project',
            'type' => 'facilitator'
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
                    'type' => ProjectParticipantEnum::facilitator->value
                ]
            ]);
    }

    public function testCanCreateProjectRequestWithLearnerOrFacilitatorTypeAsUserWithRightUserTypeToOfficial()
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

        $response = $this->postJson("api/request", [
            'toId' => $official->id,
            'toType' => 'user',
            'forId' => $for->id,
            'forType' => 'project',
            'type' => 'facilitator'
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
                    'type' => ProjectParticipantEnum::facilitator->value
                ]
            ]);
    }

    public function testCanCreateProjectRequestWithSponsorTypeAsOfficialToUserWithDonorUserType()
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

        $response = $this->postJson("api/request", [
            'toId' => $to->id,
            'toType' => 'user',
            'forId' => $for->id,
            'forType' => 'project',
            'type' => 'sponsor'
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
                    'type' => ProjectParticipantEnum::sponsor->value
                ]
            ]);
    }

    public function testCanCreateProjectRequestWithSponsorTypeAsCompanyToOfficial()
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

        $response = $this->postJson("api/request", [
            'fromId' => $company->id,
            'fromType' => 'company',
            'forId' => $for->id,
            'forType' => 'project',
            'type' => 'sponsor'
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
                    'type' => ProjectParticipantEnum::sponsor->value
                ]
            ]);
    }

    public function testCanCreateCompanyRequestWithMemberTypeAsUserToOfficial()
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
        
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($official);
        $relation->save();
        
        $this->actingAs($from);

        $response = $this->postJson("api/request", [
            'toId' => $official->id,
            'toType' => 'user',
            'forId' => $company->id,
            'forType' => 'company',
            'type' => 'member'
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
                    'type' => "MEMBER"
                ]
            ]);
    }

    public function testCanCreateCompanyRequestWithMemberTypeAsOfficialToUser()
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
        
        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($official);
        $relation->save();

        $this->actingAs($official);

        $response = $this->postJson("api/request", [
            'toId' => $potentialMember->id,
            'toType' => 'user',
            'forId' => $company->id,
            'forType' => 'company',
            'type' => 'member'
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
                    'type' => "MEMBER"
                ]
            ]);
    }

    public function testCanCreateCompanyRequestWithAdministratorTypeAsOwnerToUser()
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

        $response = $this->postJson("api/request", [
            'toId' => $potentialAdministrator->id,
            'toType' => 'user',
            'forId' => $company->id,
            'forType' => 'company',
            'type' => 'member'
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
                    'type' => "MEMBER"
                ]
            ]);
    }

    public function testCanCreateCompanyRequestWithAdministratorTypeAsUserToOwner()
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

        $response = $this->postJson("api/request", [
            'toId' => $potentialAdministrator->id,
            'toType' => 'user',
            'forId' => $company->id,
            'forType' => 'company',
            'type' => 'member'
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
                    'type' => "MEMBER"
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
                'type' => RelationshipTypeEnum::companyAdministrator->value,
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'to_type' => $potentialAdministrator::class,
            'to_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->actingAs($user);

        $response = $this->postJson("api/request/{$request->id}", [
            
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
                'type' => RelationshipTypeEnum::companyAdministrator->value,
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'to_type' => $potentialAdministrator::class,
            'to_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->actingAs($user);

        $response = $this->postJson("api/request/{$request->id}", [
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
                'type' => RelationshipTypeEnum::companyAdministrator->value,
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'to_type' => $potentialAdministrator::class,
            'to_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->actingAs($user);

        $response = $this->postJson("api/request/10", [
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
                'type' => RelationshipTypeEnum::companyAdministrator->value,
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $user::class,
            'from_id' => $user->id,
            'to_type' => $potentialAdministrator::class,
            'to_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->actingAs($potentialAdministrator);

        $response = $this->postJson("api/request/{$request->id}", [
            'response' => "accepted"
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'request' => [
                    'id' => $request->id,
                    'state' => RequestStateEnum::accepted->value,
                    'type' => RelationshipTypeEnum::companyAdministrator->value,
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

        $relation = $company->addedByRelations()->create([
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($official);
        $relation->save();

        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $potentialMember,
                'to' => $official,
                'for' => $company,
                'type' => RelationshipTypeEnum::companyMember->value,
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $potentialMember::class,
            'from_id' => $potentialMember->id,
            'to_type' => $official::class,
            'to_id' => $official->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->actingAs($user);

        $response = $this->postJson("api/request/{$request->id}", [
            'response' => "accepted"
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'request' => [
                    'id' => $request->id,
                    'state' => RequestStateEnum::accepted->value,
                    'type' => RelationshipTypeEnum::companyMember->value,
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
                'type' => RelationshipTypeEnum::companyMember->value,
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $potentialMember::class,
            'from_id' => $potentialMember->id,
            'to_type' => $user::class,
            'to_id' => $user->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->actingAs($admin);

        $response = $this->postJson("api/request/{$request->id}", [
            'response' => "accepted"
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'request' => [
                    'id' => $request->id,
                    'state' => RequestStateEnum::accepted->value,
                    'type' => RelationshipTypeEnum::companyMember->value,
                ]
            ]);
    }

    public function testCannotSendRequestToPotentialParentAsWardWithInvalidType()
    {
        $ward = $this->createUser();
        $parent = $this->createUser();

        $wrong ="wrong_type";
        $data = [
            "type" => $wrong,
            "to_type" => "user",
            "to_id" => $parent->id
        ];

        $this->actingAs($ward);

        $response = $this->postJson("/api/request", $data);

        $response->assertStatus(500)
            ->assertJson([
                "status" => false,
                "message" => "Sorry, {$wrong} is not a valid type for user relationships."
            ]);

        $this->assertFalse($parent->hasPendingRequests());
    }

    public function testCannotSendRequestToPotentialParentAsWardWithoutTo()
    {
        $ward = $this->createUser();
        $parent = $this->createUser();

        $type ="ward";
        $data = [
            "type" => $type,
        ];

        $this->actingAs($ward);

        $response = $this->postJson("/api/request", $data);

        $response->assertStatus(500)
            ->assertJson([
                "status" => false,
                "message" => "Sorry, to make a request you need the request from someone to another person, and regarding something."
            ]);
            
        $this->assertFalse($parent->hasPendingRequests());
    }

    public function testCanmotSendUserRelationshipRequestToNonUserAsWard()
    {
        $ward = $this->createUser();
        $parent = Company::factory()->create(["user_id" => $ward->id]);

        $this->actingAs($ward);

        $type = strtolower(RelationshipTypeEnum::ward->value);
        $data = [
            "type" => $type,
            "to_id" => $parent->id,
            "to_type" => strtolower(class_basename($parent)),
        ];

        $response = $this->postJson("/api/request", $data);

        $response->assertStatus(500)
            ->assertJson([
                "status" => false,
                "message" => "Sorry, a parent-ward relationship should be between two users."
            ]);
    }

    public function testCanmotSendRequestToNonAdultParentAsWard()
    {
        $ward = $this->createUser();
        $parent = $this->createUser();

        $this->actingAs($ward);

        $type = strtolower(RelationshipTypeEnum::ward->value);
        $data = [
            "type" => $type,
            "to_id" => $parent->id,
            "to_type" => strtolower(class_basename($parent)),
        ];

        $response = $this->postJson("/api/request", $data);

        $response->assertStatus(500)
            ->assertJson([
                "status" => false,
                "message" => "Sorry, you cannot send a request from/to a parent who is not an adult."
            ]);
            
        $this->assertFalse($parent->hasPendingRequests());
    }

    public function testCanSendRequestToPotentialParentAsWard()
    {
        $ward = $this->createUser();
        $parent = $this->createAdultUser();

        $this->actingAs($ward);

        $type = strtolower(RelationshipTypeEnum::ward->value);
        $data = [
            "type" => $type,
            "to_id" => $parent->id,
            "to_type" => strtolower(class_basename($parent)),
        ];

        $response = $this->postJson("/api/request", $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "request" => [
                    "to" => [
                        "id" => $parent->id,
                        "username" => $parent->username
                    ],
                    "from" => [
                        "id" => $ward->id,
                        "username" => $ward->username
                    ],
                    "type" => strtolower($type)
                ]
            ]);
            
        $this->assertTrue($parent->hasPendingRequests());
    }

    public function testCanmotSendRequestToWardAsNonAdultParent()
    {
        $ward = $this->createUser();
        $parent = $this->createUser();

        $this->actingAs($parent);

        $type = strtolower(RelationshipTypeEnum::parent->value);
        $data = [
            "type" => $type,
            "to_id" => $ward->id,
            "to_type" => strtolower(class_basename($ward)),
        ];

        $response = $this->postJson("/api/request", $data);

        $response->assertStatus(500)
            ->assertJson([
                "status" => false,
                "message" => "Sorry, you cannot send a request from/to a parent who is not an adult."
            ]);
            
        $this->assertFalse($ward->hasPendingRequests());
    }

    public function testCanmotSendRequestFromAndToTheSameUser()
    {
        $parent = $this->createUser();

        $this->actingAs($parent);

        $type = strtolower(RelationshipTypeEnum::parent->value);
        $data = [
            "type" => $type,
            "to_id" => $parent->id,
            "to_type" => strtolower(class_basename($parent)),
        ];

        $response = $this->postJson("/api/request", $data);

        $response->assertStatus(500)
            ->assertJson([
                "status" => false,
                "message" => "Sorry, requests cannot be sent from and to the same user."
            ]);
            
        $this->assertFalse($parent->hasPendingRequests());
    }

    public function testCanSendRequestToPotentialWardAsParent()
    {
        $ward = $this->createUser();
        $parent = $this->createAdultUser();

        $this->actingAs($parent);

        $type = strtolower(RelationshipTypeEnum::parent->value);
        $data = [
            "type" => $type,
            "to_id" => $ward->id,
            "to_type" => strtolower(class_basename($ward)),
        ];

        $response = $this->postJson("/api/request", $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "request" => [
                    "to" => [
                        "id" => $ward->id,
                        "username" => $ward->username
                    ],
                    "from" => [
                        "id" => $parent->id,
                        "username" => $parent->username
                    ],
                    "type" => strtolower($type)
                ]
            ]);
            
        $this->assertTrue($ward->hasPendingRequests());
    }

    public function testCanAcceptParentingRequestAsWard()
    {
        $ward = $this->createUser();
        $parent = $this->createAdultUser();

        $this->actingAs($parent);

        $type = strtolower(RelationshipTypeEnum::parent->value);
        $data = [
            "type" => $type,
            "to_id" => $ward->id,
            "to_type" => strtolower(class_basename($ward)),
        ];

        $response = $this->postJson("/api/request", $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "request" => [
                    "to" => [
                        "id" => $ward->id,
                        "username" => $ward->username
                    ],
                    "from" => [
                        "id" => $parent->id,
                        "username" => $parent->username
                    ],
                    "type" => strtolower($type),
                    "state" => strtolower(RequestStateEnum::pending->value),
                ]
            ]);
            
        $this->assertTrue($ward->hasPendingRequests());

        $state = strtolower(RequestStateEnum::accepted->value);
        $data = [
            "response" => $state,
        ];

        $this->actingAs($ward);
        $requestId = json_decode($response->baseResponse->content())->request->id;

        $response = $this->postJson("/api/request/{$requestId}", $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "request" => [
                    "to" => [
                        "id" => $ward->id,
                        "username" => $ward->username
                    ],
                    "from" => [
                        "id" => $parent->id,
                        "username" => $parent->username
                    ],
                    "type" => strtolower($type),
                    "state" => $state,
                ]
            ]);
            
        $this->assertFalse($ward->hasPendingRequests());
        $this->assertTrue($ward->isWard());
        $this->assertTrue($parent->isParent());
    }

    public function testCanAcceptWardingRequestAsParent()
    {
        $ward = $this->createUser();
        $parent = $this->createAdultUser();

        $this->actingAs($ward);

        $type = strtolower(RelationshipTypeEnum::ward->value);
        $data = [
            "type" => $type,
            "to_id" => $parent->id,
            "to_type" => strtolower(class_basename($parent)),
        ];

        $response = $this->postJson("/api/request", $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "request" => [
                    "to" => [
                        "id" => $parent->id,
                        "username" => $parent->username
                    ],
                    "from" => [
                        "id" => $ward->id,
                        "username" => $ward->username
                    ],
                    "type" => strtolower($type),
                    "state" => strtolower(RequestStateEnum::pending->value),
                ]
            ]);
            
        $this->assertTrue($parent->hasPendingRequests());

        $state = strtolower(RequestStateEnum::accepted->value);
        $data = [
            "response" => $state,
        ];

        $this->actingAs($parent);
        $requestId = json_decode($response->baseResponse->content())->request->id;

        $response = $this->postJson("/api/request/{$requestId}", $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "request" => [
                    "to" => [
                        "id" => $parent->id,
                        "username" => $parent->username
                    ],
                    "from" => [
                        "id" => $ward->id,
                        "username" => $ward->username
                    ],
                    "type" => strtolower($type),
                    "state" => $state,
                ]
            ]);
            
        $this->assertFalse($parent->hasPendingRequests());
        $this->assertTrue($ward->isWard());
        $this->assertTrue($parent->isParent());
    }

    public function testCanDeclineParentingRequestAsWard()
    {
        $ward = $this->createUser();
        $parent = $this->createAdultUser();

        $this->actingAs($parent);

        $type = strtolower(RelationshipTypeEnum::parent->value);
        $data = [
            "type" => $type,
            "to_id" => $ward->id,
            "to_type" => strtolower(class_basename($ward)),
        ];

        $response = $this->postJson("/api/request", $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "request" => [
                    "to" => [
                        "id" => $ward->id,
                        "username" => $ward->username
                    ],
                    "from" => [
                        "id" => $parent->id,
                        "username" => $parent->username
                    ],
                    "type" => strtolower($type),
                    "state" => strtolower(RequestStateEnum::pending->value),
                ]
            ]);
            
        $this->assertTrue($ward->hasPendingRequests());

        $state = strtolower(RequestStateEnum::declined->value);
        $data = [
            "response" => $state,
        ];

        $this->actingAs($ward);
        $requestId = json_decode($response->baseResponse->content())->request->id;

        $response = $this->postJson("/api/request/{$requestId}", $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "request" => [
                    "to" => [
                        "id" => $ward->id,
                        "username" => $ward->username
                    ],
                    "from" => [
                        "id" => $parent->id,
                        "username" => $parent->username
                    ],
                    "type" => strtolower($type),
                    "state" => $state,
                ]
            ]);
            
        $this->assertFalse($ward->hasPendingRequests());
        $this->assertFalse($ward->isWard());
        $this->assertFalse($parent->isParent());
    }

    public function testCanDeclineWardingRequestAsParent()
    {
        $ward = $this->createUser();
        $parent = $this->createAdultUser();

        $this->actingAs($ward);

        $type = strtolower(RelationshipTypeEnum::ward->value);
        $data = [
            "type" => $type,
            "to_id" => $parent->id,
            "to_type" => strtolower(class_basename($parent)),
        ];

        $response = $this->postJson("/api/request", $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "request" => [
                    "to" => [
                        "id" => $parent->id,
                        "username" => $parent->username
                    ],
                    "from" => [
                        "id" => $ward->id,
                        "username" => $ward->username
                    ],
                    "type" => strtolower($type),
                    "state" => strtolower(RequestStateEnum::pending->value),
                ]
            ]);
            
        $this->assertTrue($parent->hasPendingRequests());

        $state = strtolower(RequestStateEnum::declined->value);
        $data = [
            "response" => $state,
        ];

        $this->actingAs($parent);
        $requestId = json_decode($response->baseResponse->content())->request->id;

        $response = $this->postJson("/api/request/{$requestId}", $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "request" => [
                    "to" => [
                        "id" => $parent->id,
                        "username" => $parent->username
                    ],
                    "from" => [
                        "id" => $ward->id,
                        "username" => $ward->username
                    ],
                    "type" => strtolower($type),
                    "state" => $state,
                ]
            ]);
            
        $this->assertFalse($parent->hasPendingRequests());
        $this->assertFalse($ward->isWard());
        $this->assertFalse($parent->isParent());
    }
}
