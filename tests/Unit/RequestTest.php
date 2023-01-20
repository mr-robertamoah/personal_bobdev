<?php

namespace Tests\Unit;

use App\DTOs\CompanyDTO;
use App\DTOs\ProjectDTO;
use App\DTOs\RequestDTO;
use App\DTOs\ResponseDTO;
use App\Enums\ProjectParticipantEnum;
use App\Enums\RelationshipTypeEnum;
use App\Enums\RequestStateEnum;
use App\Exceptions\RequestException;
use App\Exceptions\ResponseException;
use App\Models\Company;
use App\Models\User;
use App\Models\UserType;
use App\Services\CompanyService;
use App\Services\ProjectService;
use App\Services\RequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateRequestWithoutAFor()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry, to make a request you need the request from someone to another person, and regarding something.");

        
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $from = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $to = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $from,
                'to' => $to,
                'purpose' => ProjectParticipantEnum::facilitator->value
            ])
        );
    }

    public function testCannotCreateRequestWithoutAFrom()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry, to make a request you need the request from someone to another person, and regarding something.");

        
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $from = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $to = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'for' => $for,
                'to' => $to,
                'purpose' => ProjectParticipantEnum::facilitator->value
            ])
        );
    }

    public function testCannotCreateRequestWithUnacceptableFor()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry, these set of data for this request does not meet any requirement.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $from = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $to = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $from,
                'for' => $user,
                'to' => $to,
                'purpose' => ProjectParticipantEnum::facilitator->value
            ])
        );
    }

    public function testCannotCreateRequestWithoutPurpose()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry, the purpose of the request is required.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $from = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $to = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $from,
                'for' => $user,
                'to' => $to,
            ])
        );
    }

    public function testCannotCreateProjectRequestWithoutRightPurpose()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry, hey is not a valid purpose for projects.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $from = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $to = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $from,
                'for' => $for,
                'to' => $to,
                'purpose' => 'hey'
            ])
        );
    }

    public function testOwnerCannotCreateRequestForProjectWithFacilitatorPurposeToUserNotAFacilitator()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry, you cannot send this request because the recepient is not a facilitator.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $to = User::factory()
            ->create();
        
        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $for->id,
            'participant_id' => $to->id,
            'participant_type' => $to::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $user,
                'for' => $for,
                'to' => $to,
                'purpose' => ProjectParticipantEnum::facilitator->value
            ])
        );

        $this->assertDatabaseMissing('requests', [
            'to_id' => $to->id,
            'to_type' => $to::class,
            'from_id' => $user->id,
            'from_type' => $user::class,
            'for_id' => $for->id,
            'for_type' => $for::class,
            'purpose' => ProjectParticipantEnum::facilitator->value
        ]);
    }

    public function testCannotCreateRequestForProjectWithFacilitatorPurposeWhenAlreadyFacilitator()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry, you cannot send this request because you are already a facilitator.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $from = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        (new ProjectService)->addParticipantToProject(
            ProjectDTO::new()->fromArray([
                'project' => $for,
                'participant' => $from,
                'addedby' => $user,
                'participantType' => ProjectParticipantEnum::facilitator->value
            ])
        );

        $this->assertDatabaseHas('project_participant', [
            'project_id' => $for->id,
            'participant_id' => $from->id,
            'participant_type' => $from::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $from,
                'for' => $for,
                'purpose' => ProjectParticipantEnum::facilitator->value
            ])
        );
    }

    public function testOwnerCannotCreateRequestForProjectWithStudentPurposeToUserNotStudent()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry, you cannot send this request because the recepient is not a learner.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $to = User::factory()->create();
        
        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $for->id,
            'participant_id' => $to->id,
            'participant_type' => $to::class,
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $user,
                'for' => $for,
                'to' => $to,
                'purpose' => ProjectParticipantEnum::learner->value
            ])
        );
    }

    public function testCannotCreateRequestForProjectWithStudentPurposeWhenAlreadyStudent()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry, you cannot send this request because you are already a learner.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $from = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();
        
        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        (new ProjectService)->addParticipantToProject(
            ProjectDTO::new()->fromArray([
                'project' => $for,
                'participant' => $from,
                'addedby' => $user,
                'participantType' => ProjectParticipantEnum::learner->value
            ])
        );

        $this->assertDatabaseHas('project_participant', [
            'project_id' => $for->id,
            'participant_id' => $from->id,
            'participant_type' => $from::class,
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $from,
                'for' => $for,
                'purpose' => ProjectParticipantEnum::learner->value
            ])
        );
    }

    public function testCannotCreateRequestForProjectWithoutAnOfficialEitherSendingOrReceiving()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry, this request has to be from or to an official of the project.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $from = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $to = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $from,
                'for' => $for,
                'to' => $to,
                'purpose' => ProjectParticipantEnum::facilitator->value
            ])
        );

        $this->assertDatabaseMissing('requests', [
            'from_type' => $from::class,
            'from_id' => $from->id,
            'to_type' => $to::class,
            'to_id' => $to->id,
            'for_type' => $for::class,
            'for_id' => $for->id,
            'purpose' => ProjectParticipantEnum::facilitator->value
        ]);
    }

    public function testCanCreateRequestForProjectWithFacilitatorPurposeWhenUserHasFacilitatorUserType()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $from = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $for->id,
            'participant_id' => $from->id,
            'participant_type' => $from::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $from,
                'for' => $for,
                'purpose' => ProjectParticipantEnum::facilitator->value
            ])
        );

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'to_type' => $for->owner::class,
            'to_id' => $for->owner->id,
            'for_type' => $for::class,
            'for_id' => $for->id,
            'from_type' => $from::class,
            'from_id' => $from->id,
            'purpose' => ProjectParticipantEnum::facilitator->value
        ]);
    }

    public function testCanCreateRequestForProjectWithFacilitatorPurposeWhenOfficialToUserHasFacilitatorUserType()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $to = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $for->id,
            'participant_id' => $to->id,
            'participant_type' => $to::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $user,
                'to' => $to,
                'for' => $for,
                'purpose' => ProjectParticipantEnum::facilitator->value
            ])
        );

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'to_type' => $to::class,
            'to_id' => $to->id,
            'for_type' => $for::class,
            'for_id' => $for->id,
            'from_type' => $user::class,
            'from_id' => $user->id,
            'purpose' => ProjectParticipantEnum::facilitator->value
        ]);
    }

    public function testCanCreateRequestAsOfficialForProjectWithSponsorPurposeToCompany()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();
        $companyOwner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $company = Company::factory()->create([
            'user_id' => $companyOwner->id,
        ]);
        
        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $for->id,
            'participant_id' => $company->id,
            'participant_type' => $company::class,
            'participating_as' => ProjectParticipantEnum::sponsor->value
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $user,
                'to' => $company,
                'for' => $for,
                'purpose' => ProjectParticipantEnum::sponsor->value
            ])
        );

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'to_type' => $company::class,
            'to_id' => $company->id,
            'for_type' => $for::class,
            'for_id' => $for->id,
            'from_type' => $user::class,
            'from_id' => $user->id,
            'purpose' => ProjectParticipantEnum::sponsor->value
        ]);
    }

    public function testCanCreateRequestAsCompanyForProjectWithSponsorPurposeToOfficial()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();
        $companyOwner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();

        $company = Company::factory()->create([
            'user_id' => $companyOwner->id,
        ]);
        
        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $for->id,
            'participant_id' => $company->id,
            'participant_type' => $company::class,
            'participating_as' => ProjectParticipantEnum::sponsor->value
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $company,
                'for' => $for,
                'purpose' => ProjectParticipantEnum::sponsor->value
            ])
        );

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'from_type' => $company::class,
            'from_id' => $company->id,
            'for_type' => $for::class,
            'for_id' => $for->id,
            'to_type' => $user::class,
            'to_id' => $user->id,
            'purpose' => ProjectParticipantEnum::sponsor->value
        ]);
    }

    public function testCanCreateRequestAsOfficialForProjectWithSponsorPurposeToUserWithDonorUserType()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();
        $donor = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::DONOR
            ]), [], 'userTypes')
            ->create();
        
        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $for->id,
            'participant_id' => $donor->id,
            'participant_type' => $donor::class,
            'participating_as' => ProjectParticipantEnum::sponsor->value
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $user,
                'to' => $donor,
                'for' => $for,
                'purpose' => ProjectParticipantEnum::sponsor->value
            ])
        );

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'to_type' => $donor::class,
            'to_id' => $donor->id,
            'for_type' => $for::class,
            'for_id' => $for->id,
            'from_type' => $user::class,
            'from_id' => $user->id,
            'purpose' => ProjectParticipantEnum::sponsor->value
        ]);
    }

    public function testCanCreateRequestAsUserWithDonorUserTypeForProjectWithDonorPurposeToOfficial()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();
        $donor = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::DONOR
            ]), [], 'userTypes')
            ->create();
        
        $for = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $for->id,
            'participant_id' => $donor->id,
            'participant_type' => $donor::class,
            'participating_as' => ProjectParticipantEnum::sponsor->value
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $donor,
                'for' => $for,
                'purpose' => ProjectParticipantEnum::sponsor->value
            ])
        );

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'from_type' => $donor::class,
            'from_id' => $donor->id,
            'for_type' => $for::class,
            'for_id' => $for->id,
            'to_type' => $user::class,
            'to_id' => $user->id,
            'purpose' => ProjectParticipantEnum::sponsor->value
        ]);
    }

    public function testCannotCreateCompanyRequestWithoutRightPurpose()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry, hey is not a valid purpose for companies.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $from = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $from,
                'for' => $company,
                'purpose' => 'hey'
            ])
        );
    }

    public function testCannotCreateCompanyRequestAsUserWithAdministratorPurposeWhenNonAdult()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry! Amoah Robert must be an adult in order to have such a relationship with a company.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $from = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(10)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $from,
                'for' => $company,
                'purpose' => 'administrator'
            ])
        );
    }

    public function testCannotCreateCompanyRequestAsCompanyOfficialWithAdministratorPurposeToNonAdultUser()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry! Amoah Robert must be an adult in order to have such a relationship with a company.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(10)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $user,
                'for' => $company,
                'to' => $potentialAdministrator,
                'purpose' => 'administrator'
            ])
        );
    }

    public function testCannotCreateCompanyRequestAsMemberWithAdministratorPurposeWhenAlreadingParticipatingInCompany()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry! Amoah Robert is already a member of the great enterprise company.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $administrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$administrator->id => RelationshipTypeEnum::companyMember->value]
            ])
        );

        $this->assertDatabaseHas('relations', [
            'relationship_type' => RelationshipTypeEnum::companyMember->value,
            'to_type' => $administrator::class,
            'to_id' => $administrator->id,
            'by_type' => $company::class,
            'by_id' => $company->id,
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $administrator,
                'for' => $company,
                'purpose' => 'administrator'
            ])
        );
    }

    public function testCannotCreateCompanyRequestAsUserWithAdministratorPurposeWhenAlreadyAnAdministrator()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry! Both sender and recepient cannot be officials of the company with name the great enterprise company.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $administrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$administrator->id => RelationshipTypeEnum::companyAdministrator->value]
            ])
        );

        $this->assertDatabaseHas('relations', [
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value,
            'to_type' => $administrator::class,
            'to_id' => $administrator->id,
            'by_type' => $company::class,
            'by_id' => $company->id,
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $administrator,
                'for' => $company,
                'purpose' => 'administrator'
            ])
        );
    }

    public function testCannotCreateCompanyRequestAsOfficialWithAdministratorPurposeToUserAlreadingParticipatingInCompany()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry! Amoah Robert is already a member of the great enterprise company.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $member = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$member->id => RelationshipTypeEnum::companyMember->value]
            ])
        );

        $this->assertDatabaseHas('relations', [
            'relationship_type' => RelationshipTypeEnum::companyMember->value,
            'to_type' => $member::class,
            'to_id' => $member->id,
            'by_type' => $company::class,
            'by_id' => $company->id,
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $user,
                'to' => $member,
                'for' => $company,
                'purpose' => 'administrator'
            ])
        );
    }

    public function testCannotCreateCompanyRequestAsOfficialWithAdministratorPurposeToUserAlreadyAnAdministrator()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry! Both sender and recepient cannot be officials of the company with name the great enterprise company.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $administrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$administrator->id => RelationshipTypeEnum::companyAdministrator->value]
            ])
        );

        $this->assertDatabaseHas('relations', [
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value,
            'to_type' => $administrator::class,
            'to_id' => $administrator->id,
            'by_type' => $company::class,
            'by_id' => $company->id,
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $user,
                'to' => $administrator,
                'for' => $company,
                'purpose' => 'administrator'
            ])
        );
    }

    public function testCannotCreateCompanyRequestWithAdministratorPurposeAsUserWithoutSendingItToANonOfficial()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry! The sender or recepient must be an official of the company with name the great enterprise company.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $nonMember = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $potentialAdministrator,
                'for' => $company,
                'to' => $nonMember,
                'purpose' => 'administrator'
            ])
        );
    }

    public function testCannotCreateCompanyRequestWithAdministratorPurposeAsNonOfficialToUser()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry! The sender or recepient must be an official of the company with name the great enterprise company.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $nonMember = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'to' => $potentialAdministrator,
                'for' => $company,
                'from' => $nonMember,
                'purpose' => 'administrator'
            ])
        );
    }

    public function testCannotCreateCompanyRequestWithAdminstratorPurposeAsUserToManager()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to perform this action on the company with name the great enterprise.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $manager = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$manager->id => RelationshipTypeEnum::companyAdministrator->value]
            ])
        );

        $this->assertDatabaseHas('relations', [
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value,
            'to_type' => $manager::class,
            'to_id' => $manager->id,
            'by_type' => $company::class,
            'by_id' => $company->id,
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $potentialAdministrator,
                'for' => $company,
                'to' => $manager,
                'purpose' => 'administrator'
            ])
        );
    }

    public function testCannotCreateCompanyRequestWithAdminstratorPurposeAsManagerToUser()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to perform this action on the company with name the great enterprise.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $manager = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'companyId' => $company->id,
                'memberships' => [$manager->id => RelationshipTypeEnum::companyAdministrator->value]
            ])
        );

        $this->assertDatabaseHas('relations', [
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value,
            'to_type' => $manager::class,
            'to_id' => $manager->id,
            'by_type' => $company::class,
            'by_id' => $company->id,
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $manager,
                'for' => $company,
                'to' => $potentialAdministrator,
                'purpose' => 'administrator'
            ])
        );
    }

    public function testCannotCreateCompanyRequestWithAdminstratorPurposeAsAdminToUser()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to perform this action on the company with name the great enterprise.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $admin = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $admin,
                'for' => $company,
                'to' => $potentialAdministrator,
                'purpose' => 'administrator'
            ])
        );
    }

    public function testCannotCreateCompanyRequestWithAdminstratorPurposeAsUserToAdmin()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to perform this action on the company with name the great enterprise.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $admin = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $potentialAdministrator,
                'for' => $company,
                'to' => $admin,
                'purpose' => 'administrator'
            ])
        );
    }

    public function testCanCreateCompanyRequestWithMemberPurposeAsUserToManager()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $manager = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialMember = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'companyId' => $company->id,
                'memberships' => [$manager->id => RelationshipTypeEnum::companyAdministrator->value],
                'userId' => $user->id,
            ])
        );

        $this->assertDatabaseHas('relations', [
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value,
            'to_type' => $manager::class,
            'to_id' => $manager->id,
            'by_type' => $company::class,
            'by_id' => $company->id,
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $potentialMember,
                'for' => $company,
                'to' => $manager,
                'purpose' => 'member'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'to_type' => $manager::class,
            'to_id' => $manager->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'from_type' => $potentialMember::class,
            'from_id' => $potentialMember->id,
        ]);

        $this->assertTrue(in_array(strtolower($request->purpose), RelationshipTypeEnum::COMPANYMEMBERALIASES));
    }

    public function testCanCreateCompanyRequestWithMemberPurposeAsManagerToUser()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $manager = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialMember = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $company = (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'companyId' => $company->id,
                'memberships' => [$manager->id => RelationshipTypeEnum::companyAdministrator->value],
                'userId' => $user->id,
            ])
        );

        $this->assertDatabaseHas('relations', [
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value,
            'to_type' => $manager::class,
            'to_id' => $manager->id,
            'by_type' => $company::class,
            'by_id' => $company->id,
        ]);
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $manager,
                'for' => $company,
                'to' => $potentialMember,
                'purpose' => 'member'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'to_type' => $potentialMember::class,
            'to_id' => $potentialMember->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'from_type' => $manager::class,
            'from_id' => $manager->id,
        ]);

        $this->assertTrue(in_array(strtolower($request->purpose), RelationshipTypeEnum::COMPANYMEMBERALIASES));
    }

    public function testCanCreateCompanyRequestWithMemberPurposeAsUserToOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialMember = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $potentialMember,
                'for' => $company,
                'to' => $user,
                'purpose' => 'member'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'to_type' => $user::class,
            'to_id' => $user->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'from_type' => $potentialMember::class,
            'from_id' => $potentialMember->id,
        ]);

        $this->assertTrue(in_array(strtolower($request->purpose), RelationshipTypeEnum::COMPANYMEMBERALIASES));
    }

    public function testCanCreateCompanyRequestWithMemberPurposeAsOwnerToUser()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialMember = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $user,
                'for' => $company,
                'to' => $potentialMember,
                'purpose' => 'member'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'to_type' => $potentialMember::class,
            'to_id' => $potentialMember->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'from_type' => $user::class,
            'from_id' => $user->id,
        ]);

        $this->assertTrue(in_array(strtolower($request->purpose), RelationshipTypeEnum::COMPANYMEMBERALIASES));
    }

    public function testCanCreateCompanyRequestWithAdministratorPurposeAsOwnerToUser()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $user,
                'for' => $company,
                'to' => $potentialAdministrator,
                'purpose' => 'administrator'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'to_type' => $potentialAdministrator::class,
            'to_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'from_type' => $user::class,
            'from_id' => $user->id,
        ]);

        $this->assertTrue(in_array(strtolower($request->purpose), RelationshipTypeEnum::COMPANYADMINISTRATORALIASES));
    }

    public function testCanCreateCompanyRequestWithAdministratorPurposeAsUserToOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'to' => $user,
                'for' => $company,
                'from' => $potentialAdministrator,
                'purpose' => 'administrator'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $potentialAdministrator::class,
            'from_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $user::class,
            'to_id' => $user->id,
        ]);

        $this->assertTrue(in_array(strtolower($request->purpose), RelationshipTypeEnum::COMPANYADMINISTRATORALIASES));
    }

    public function testCanCreateProjectRequestWithLearnerPurposeToFacilitatorOfProject()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $facilitator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialLearner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP tutorial',
                'description' => 'this is the description of this project',
                'addedby' => $user,
            ])
        );
        
        (new ProjectService)->addParticipantToProject(
            ProjectDTO::new()->fromArray([
                'project' => $project,
                'participant' => $facilitator,
                'participantType' => 'facilitator',
                'addedby' => $user,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'to' => $facilitator,
                'for' => $project,
                'from' => $potentialLearner,
                'purpose' => 'learner'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $potentialLearner::class,
            'from_id' => $potentialLearner->id,
            'for_type' => $project::class,
            'for_id' => $project->id,
            'to_type' => $facilitator::class,
            'to_id' => $facilitator->id,
            'state' => RequestStateEnum::pending->value
        ]);
    }

    public function testCannotRespondToRequestWithoutValidRequest()
    {
        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage("Sorry! You need a request to respond to. No request was found.");
        
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'to' => $user,
                'for' => $company,
                'from' => $potentialAdministrator,
                'purpose' => 'administrator'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $potentialAdministrator::class,
            'from_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $user::class,
            'to_id' => $user->id,
            'state' => RequestStateEnum::pending->value
        ]);

        (new RequestService)->respondToRequest(
            ResponseDTO::new()->fromArray([
                'response' => null,
                'userId' => $user->id
            ])
        );
    }

    public function testCannotRespondToRequestWithoutAResponse()
    {
        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage("Sorry! A response is required to respond to a request.");
        
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'to' => $user,
                'for' => $company,
                'from' => $potentialAdministrator,
                'purpose' => 'administrator'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $potentialAdministrator::class,
            'from_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $user::class,
            'to_id' => $user->id,
            'state' => RequestStateEnum::pending->value
        ]);

        (new RequestService)->respondToRequest(
            ResponseDTO::new()->fromArray([
                'requestId' => $request->id,
                'userId' => $user->id
            ])
        );
    }

    public function testCannotRespondToRequestWithoutAppropriateResponse()
    {
        $response = "answer";

        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage("Sorry! {$response} is not a valid response for a request.");
        
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'to' => $user,
                'for' => $company,
                'from' => $potentialAdministrator,
                'purpose' => 'administrator'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $potentialAdministrator::class,
            'from_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $user::class,
            'to_id' => $user->id,
            'state' => RequestStateEnum::pending->value
        ]);

        (new RequestService)->respondToRequest(
            ResponseDTO::new()->fromArray([
                'requestId' => $request->id,
                'response' => $response,
                'userId' => $user->id
            ])
        );
    }

    public function testCannotRespondToRequestWithoutBeenRespondentOrAdminOrOfficial()
    {
        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage("Sorry, you are not authorized to respond to this request.");
        
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $otherUser = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'to' => $user,
                'for' => $company,
                'from' => $potentialAdministrator,
                'purpose' => 'administrator'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $potentialAdministrator::class,
            'from_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $user::class,
            'to_id' => $user->id,
            'state' => RequestStateEnum::pending->value
        ]);

        (new RequestService)->respondToRequest(
            ResponseDTO::new()->fromArray([
                'requestId' => $request->id,
                'response' => RequestStateEnum::accepted->value,
                'userId' => $otherUser->id
            ])
        );
    }

    public function testCanRespondToCompanyRequestWhenRespondent()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'to' => $user,
                'for' => $company,
                'from' => $potentialAdministrator,
                'purpose' => 'administrator'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $potentialAdministrator::class,
            'from_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $user::class,
            'to_id' => $user->id,
            'state' => RequestStateEnum::pending->value
        ]);

        $request = (new RequestService)->respondToRequest(
            ResponseDTO::new()->fromArray([
                'requestId' => $request->id,
                'response' => RequestStateEnum::accepted->value,
                'userId' => $user->id
            ])
        );

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $user::class,
            'performedby_id' => $user->id,
            'performedon_type' => $request::class,
            'performedon_id' => $request->id,
            'action' => 'respond',
            'data' => json_encode(['response' => RequestStateEnum::accepted->value])
        ]);

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $potentialAdministrator::class,
            'to_id' => $potentialAdministrator->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->assertEquals(RequestStateEnum::accepted->value, $request->state);
    }

    public function testCanRespondToCompanyRequestWhenAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $admin = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialAdministrator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'to' => $user,
                'for' => $company,
                'from' => $potentialAdministrator,
                'purpose' => 'administrator'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $potentialAdministrator::class,
            'from_id' => $potentialAdministrator->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $user::class,
            'to_id' => $user->id,
            'state' => RequestStateEnum::pending->value
        ]);

        $request = (new RequestService)->respondToRequest(
            ResponseDTO::new()->fromArray([
                'requestId' => $request->id,
                'response' => RequestStateEnum::accepted->value,
                'userId' => $admin->id
            ])
        );

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $admin::class,
            'performedby_id' => $admin->id,
            'performedon_type' => $request::class,
            'performedon_id' => $request->id,
            'action' => 'respond',
            'data' => json_encode(['response' => RequestStateEnum::accepted->value])
        ]);

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $potentialAdministrator::class,
            'to_id' => $potentialAdministrator->id,
            'relationship_type' => RelationshipTypeEnum::companyAdministrator->value
        ]);

        $this->assertEquals(RequestStateEnum::accepted->value, $request->state);
    }

    public function testCanRespondToCompanyRequestWhenNotRespondentButOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $official = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialMember = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'name' => 'the great enterprise',
                'alias' => 'tgenterprise',
                'userId' => $user->id,
            ])
        );
        
        (new CompanyService)->addMembers(
            CompanyDTO::new()->fromArray([
                'user' => $user,
                'company' => $company,
                'memberships' => [$official->id => RelationshipTypeEnum::companyAdministrator->value],
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'to' => $official,
                'for' => $company,
                'from' => $potentialMember,
                'purpose' => 'member'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $potentialMember::class,
            'from_id' => $potentialMember->id,
            'for_type' => $company::class,
            'for_id' => $company->id,
            'to_type' => $official::class,
            'to_id' => $official->id,
            'state' => RequestStateEnum::pending->value
        ]);

        $request = (new RequestService)->respondToRequest(
            ResponseDTO::new()->fromArray([
                'requestId' => $request->id,
                'response' => RequestStateEnum::accepted->value,
                'userId' => $user->id
            ])
        );

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $user::class,
            'performedby_id' => $user->id,
            'performedon_type' => $request::class,
            'performedon_id' => $request->id,
            'action' => 'respond',
            'data' => json_encode(['response' => RequestStateEnum::accepted->value])
        ]);

        $this->assertDatabaseHas('relations', [
            'by_type' => $company::class,
            'by_id' => $company->id,
            'to_type' => $potentialMember::class,
            'to_id' => $potentialMember->id,
            'relationship_type' => RelationshipTypeEnum::companyMember->value
        ]);

        $this->assertEquals(RequestStateEnum::accepted->value, $request->state);
    }

    public function testCanRespondToProjectRequestWhenRespondent()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialFacilitator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP tutorial',
                'description' => 'this is the description of this project',
                'addedby' => $user,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'from' => $user,
                'for' => $project,
                'to' => $potentialFacilitator,
                'purpose' => 'facilitator'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'to_type' => $potentialFacilitator::class,
            'to_id' => $potentialFacilitator->id,
            'for_type' => $project::class,
            'for_id' => $project->id,
            'frpm_type' => $user::class,
            'frpm_id' => $user->id,
            'state' => RequestStateEnum::pending->value
        ]);

        $request = (new RequestService)->respondToRequest(
            ResponseDTO::new()->fromArray([
                'requestId' => $request->id,
                'response' => RequestStateEnum::accepted->value,
                'userId' => $user->id
            ])
        );

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $user::class,
            'performedby_id' => $user->id,
            'performedon_type' => $request::class,
            'performedon_id' => $request->id,
            'action' => 'respond',
            'data' => json_encode(['response' => RequestStateEnum::accepted->value])
        ]);

        $this->assertDatabaseHas('project_participant', [
            'project_id' => $project->id,
            'participant_type' => $potentialFacilitator::class,
            'participant_id' => $potentialFacilitator->id,
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);

        $this->assertEquals(RequestStateEnum::accepted->value, $request->state);
    }

    public function testCanRespondToProjectRequestWhenAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $admin = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialLearner = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP tutorial',
                'description' => 'this is the description of this project',
                'addedby' => $user,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'to' => $user,
                'for' => $project,
                'from' => $potentialLearner,
                'purpose' => 'learner'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $potentialLearner::class,
            'from_id' => $potentialLearner->id,
            'for_type' => $project::class,
            'for_id' => $project->id,
            'to_type' => $user::class,
            'to_id' => $user->id,
            'state' => RequestStateEnum::pending->value
        ]);

        $request = (new RequestService)->respondToRequest(
            ResponseDTO::new()->fromArray([
                'requestId' => $request->id,
                'response' => RequestStateEnum::accepted->value,
                'userId' => $admin->id
            ])
        );

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $admin::class,
            'performedby_id' => $admin->id,
            'performedon_type' => $request::class,
            'performedon_id' => $request->id,
            'action' => 'respond',
            'data' => json_encode(['response' => RequestStateEnum::accepted->value])
        ]);

        $this->assertDatabaseHas('project_participant', [
            'project_id' => $project->id,
            'participant_type' => $potentialLearner::class,
            'participant_id' => $potentialLearner->id,
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);

        $this->assertEquals(RequestStateEnum::accepted->value, $request->state);
    }

    public function testCanRespondToProjectRequestWithLearnerPurposeWhenNotRespondentButOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $official = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create([
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);

        $potentialMember = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create([
                'first_name' => 'Robert',
                'surname' => 'Amoah',
                'dob' => now()->subYears(20)->toDateTimeString()
            ]);
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP tutorial',
                'description' => 'this is the description of this project',
                'addedby' => $user,
            ])
        );

        (new ProjectService)->addParticipantToProject(
            ProjectDTO::new()->fromArray([
                'participant' => $official,
                'participantType' => 'facilitator',
                'project' => $project,
                'addedby' => $user,
            ])
        );
        
        $request = (new RequestService)->createRequest(
            RequestDTO::new()->fromArray([
                'to' => $official,
                'for' => $project,
                'from' => $potentialMember,
                'purpose' => 'learner'
            ])
        );

        $this->assertDatabaseHas('requests', [
            'from_type' => $potentialMember::class,
            'from_id' => $potentialMember->id,
            'for_type' => $project::class,
            'for_id' => $project->id,
            'to_type' => $official::class,
            'to_id' => $official->id,
            'state' => RequestStateEnum::pending->value
        ]);

        $request = (new RequestService)->respondToRequest(
            ResponseDTO::new()->fromArray([
                'requestId' => $request->id,
                'response' => RequestStateEnum::accepted->value,
                'userId' => $user->id
            ])
        );

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $user::class,
            'performedby_id' => $user->id,
            'performedon_type' => $request::class,
            'performedon_id' => $request->id,
            'action' => 'respond',
            'data' => json_encode(['response' => RequestStateEnum::accepted->value])
        ]);

        $this->assertDatabaseHas('project_participant', [
            'project_id' => $project->id,
            'participant_type' => $potentialMember::class,
            'participant_id' => $potentialMember->id,
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);

        $this->assertEquals(RequestStateEnum::accepted->value, $request->state);
    }
}
