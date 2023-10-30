<?php

namespace Tests\Feature;

use App\Enums\ProjectParticipantEnum;
use App\Enums\ProjectSessionPeriodEnum;
use App\Enums\ProjectSessionTypeEnum;
use App\Enums\RelationshipTypeEnum;
use App\Enums\UserTypeEnum;
use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectSession;
use App\Models\UserType;
use App\Traits\TestTrait;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectSessionTest extends TestCase
{
    use RefreshDatabase;
    use TestTrait;
    use WithFaker;

    public function testCannotCreateProjectSessionAsGuest()
    {
        $response = $this->postJson('/api/project_session');
        
        $response->assertStatus(401)
            ->assertJson([
                "message" => "Unauthenticated."
            ]);
    }

    public function testCannotCreateProjectSessionWithoutValidData()
    {
        $user = $this->createUser();

        $this->actingAs($user);

        $response = $this->postJson('/api/project_session');
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => "The project id field is required. (and 4 more errors)",
                "errors" => [
                    "project_id" => ["The project id field is required."],
                    "name" => ["The name field is required."],
                    "day_of_week" => ["The day of week field is required."],
                    "type" => ["The type field is required."],
                    "period" => ["The period field is required."]
                ]
            ]);
    }

    public function testCannotCreateProjectSessionWithInvalidProjectId()
    {
        $user = $this->createUser();

        $this->actingAs($user);

        $data = [
            "name" => $this->faker->name(),
            "project_id" => 1,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
        ];

        $response = $this->postJson('/api/project_session', $data);
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => 'Sorry! A valid project is required to perform this action.',
            ]);
    }

    public function testCannotCreateProjectSessionWhenNotAuthorized()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_type" => $creator::class,
            "addedby_id" => $creator->id,
        ]);

        $this->actingAs($user);

        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
        ];

        $response = $this->postJson('/api/project_session', $data);
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => "Sorry! You are not authorized to update a project.",
            ]);
    }

    public function testCannotCreateProjectSessionWithoutStartOrEndDatesForProjectWithStartAndEndDates()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_type" => $creator::class,
            "addedby_id" => $creator->id,
            "start_date" => now()->subYears(2),
            "end_date" => now()->addYears(2),
        ]);

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($user);
        $participation->save();

        $this->actingAs($user);

        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
        ];

        $response = $this->postJson('/api/project_session', $data);
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => 'Sorry! You need to set the start date for the session.',
            ]);
    }

    public function testCannotCreateProjectSessionWhenStartDateComesBeforeToday()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_type" => $creator::class,
            "addedby_id" => $creator->id,
            "start_date" => $startDate = now()->subYears(2),
            "end_date" => $endDate = now()->addYears(2),
        ]);

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($user);
        $participation->save();

        $this->actingAs($user);

        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => now()->subDay()->toDateTimeString(),
            "end_date" => $endDate->subDay()->toDateTimeString(),
        ];

        $startDate->addDay();
        $endDate->addDay();

        $response = $this->postJson('/api/project_session', $data);
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => "Sorry! The start date for the session should be on or after today.",
            ]);
    }

    public function testCannotCreateProjectSessionWhenStartDateComesBeforeProjectStartDate()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_type" => $creator::class,
            "addedby_id" => $creator->id,
            "start_date" => $startDate = now()->addDays(2),
            "end_date" => $endDate = now()->addYears(2),
        ]);

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($user);
        $participation->save();

        $this->actingAs($user);

        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => $startDate->subDay()->toDateTimeString(),
            "end_date" => $endDate->subDay()->toDateTimeString(),
        ];

        $startDate->addDay();
        $endDate->addDay();

        $response = $this->postJson('/api/project_session', $data);
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => "Sorry! The start date for the session should come after or on {$startDate->toDateTimeString()} date.",
            ]);
    }

    public function testCannotCreateProjectSessionWhenEndDateComesBeforeProjectEndDate()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_type" => $creator::class,
            "addedby_id" => $creator->id,
            "start_date" => $startDate = now()->addDays(2),
            "end_date" => $endDate = now()->addYears(2),
        ]);

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($user);
        $participation->save();

        $this->actingAs($user);

        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => $startDate->addDay()->toDateTimeString(),
            "end_date" => $endDate->addDay()->toDateTimeString(),
        ];

        $startDate->subDay();
        $endDate->subDay();

        $response = $this->postJson('/api/project_session', $data);
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => "Sorry! The end date for the session should come before or on {$endDate->toDateTimeString()} date.",
            ]);
    }

    public function testCannotCreateProjectSessionWhenEndDateComesBeforeStartDate()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_type" => $creator::class,
            "addedby_id" => $creator->id,
            "start_date" => $startDate = now()->addDays(2),
            "end_date" => $endDate = now()->addYears(2),
        ]);

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($user);
        $participation->save();

        $this->actingAs($user);

        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => $startDate->addDay()->toDateTimeString(),
            "end_date" => $startDate->subDay()->toDateTimeString(),
        ];

        $response = $this->postJson('/api/project_session', $data);
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => "Sorry! The end date for the session should be either the same day as or after the start date.",
            ]);
    }

    public function testCannotCreateProjectSessionWhenEndTimeIsNotAtLeastAnHourAfterStartTime()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_type" => $creator::class,
            "addedby_id" => $creator->id,
            "start_date" => $startDate = now()->addDays(2),
            "end_date" => $endDate = now()->addYears(2),
        ]);

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($user);
        $participation->save();

        $this->actingAs($user);

        $startTime = now()->addHour();
        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => $startDate->addDay()->toDateTimeString(),
            "end_date" => $endDate->subDay()->toDateTimeString(),
            "start_time" => $startTime->toTimeString(),
            "end_time" => $startTime->subHour()->addMinutes(30)->toTimeString(),
        ];

        $response = $this->postJson('/api/project_session', $data);
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => "Sorry! The end time for the session should be at least an hour after the start time.",
            ]);
    }

    public function testCanCreateProjectSessionWhenOwner()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_type" => $creator::class,
            "addedby_id" => $creator->id,
            "start_date" => $startDate = now()->addDays(2),
            "end_date" => $endDate = now()->addYears(2),
        ]);

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($user);
        $participation->save();

        $this->actingAs($creator);

        $startTime = now()->addHour();
        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => $startDate->addDay()->toDateTimeString(),
            "end_date" => $endDate->subDay()->toDateTimeString(),
            "start_time" => $startTime->toTimeString(),
            "end_time" => $startTime->addMinutes(60)->addMinutes(30)->toTimeString(),
        ];

        $response = $this->postJson('/api/project_session', $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "projectSession" => [
                    "name" => $data["name"],
                    "startDate" => Carbon::parse($data["start_date"])->diffForHumans(),
                    "endDate" => Carbon::parse($data["end_date"])->diffForHumans(),
                    "startTime" => Carbon::parse($data["start_time"])->diffForHumans(),
                    "endTime" => Carbon::parse($data["end_time"])->diffForHumans(),
                ],
            ]);

        $this->assertDatabaseHas("project_sessions", [
            "project_id" => $project->id,
            "user_id" => $creator->id,
            "name" => $data["name"],
        ]);
    }

    public function testCanCreateProjectSessionWhenfacilitator()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_type" => $creator::class,
            "addedby_id" => $creator->id,
            "start_date" => $startDate = now()->addDays(2),
            "end_date" => $endDate = now()->addYears(2),
        ]);

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($user);
        $participation->save();

        $this->actingAs($user);

        $startTime = now()->addHour();
        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => $startDate->addDay()->toDateTimeString(),
            "end_date" => $endDate->subDay()->toDateTimeString(),
            "start_time" => $startTime->toTimeString(),
            "end_time" => $startTime->addMinutes(60)->addMinutes(30)->toTimeString(),
        ];

        $response = $this->postJson('/api/project_session', $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "projectSession" => [
                    "name" => $data["name"],
                    "startDate" => Carbon::parse($data["start_date"])->diffForHumans(),
                    "endDate" => Carbon::parse($data["end_date"])->diffForHumans(),
                    "startTime" => Carbon::parse($data["start_time"])->diffForHumans(),
                    "endTime" => Carbon::parse($data["end_time"])->diffForHumans(),
                ],
            ]);

        $this->assertDatabaseHas("project_sessions", [
            "project_id" => $project->id,
            "user_id" => $user->id,
            "name" => $data["name"],
        ]);
    }

    public function testCanCreateProjectSessionWhenAdmin()
    {
        $admin = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_type" => $creator::class,
            "addedby_id" => $creator->id,
            "start_date" => $startDate = now()->addDays(2),
            "end_date" => $endDate = now()->addYears(2),
        ]);

        $userType = UserType::factory()->create([
            "name" => UserTypeEnum::admin->value
        ]);
        $admin->userTypes()->attach($userType->id);

        $this->actingAs($admin);

        $startTime = now()->addHour();
        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => $startDate->addDay()->toDateTimeString(),
            "end_date" => $endDate->subDay()->toDateTimeString(),
            "start_time" => $startTime->toTimeString(),
            "end_time" => $startTime->addMinutes(60)->addMinutes(30)->toTimeString(),
        ];

        $response = $this->postJson('/api/project_session', $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "projectSession" => [
                    "name" => $data["name"],
                    "startDate" => Carbon::parse($data["start_date"])->diffForHumans(),
                    "endDate" => Carbon::parse($data["end_date"])->diffForHumans(),
                    "startTime" => Carbon::parse($data["start_time"])->diffForHumans(),
                    "endTime" => Carbon::parse($data["end_time"])->diffForHumans(),
                ],
            ]);

        $this->assertDatabaseHas("project_sessions", [
            "project_id" => $project->id,
            "user_id" => $admin->id,
            "name" => $data["name"],
        ]);
    }

    public function testCannotUpdateProjectSessionWithoutValidData()
    {
        $user = $this->createUser();
        $project = Project::factory()->create([
            "addedby_id" => $user->id,
            "addedby_type" => $user::class,
        ]);
        $projectSession = ProjectSession::factory()->create([
            "project_id" => $project->id,
            "user_id" => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/project_session/{$projectSession->id}");
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => "The name field is required when none of day of week / description / start date / end date / type / period / start time / end time are present. (and 7 more errors)",
                "errors" => [
                    "name" => ["The name field is required when none of day of week / description / start date / end date / type / period / start time / end time are present."],
                    "description" => ["The description field is required when none of day of week / name / start date / end date / type / period / start time / end time are present."],
                    "start_date" => ["The start date field is required when none of day of week / description / name /  / end date / type / period / start time / end time are present."],
                    "end_date" => ["The end date field is required when none of day of week / description / name / start date / type / period / start time / end time are present."],
                    "start_time" => ["The start time field is required when none of day of week / description / name / start date / end date / type / period / end time are present."],
                    "end_time" => ["The end time field is required when none of day of week / description / name / start date / end date / type / period / start time are present."],
                    "type" => ["The type field is required when none of day of week / description / name / start date / end date / period / start time / end time are present."],
                    "period" => ["The period field is required when none of day of week / description / name / start date / end date / type / start time / end time are present."]
                ]
            ]);
    }

    public function testCannotUpdateProjectSessionWithInvalidProjectSessionId()
    {
        $user = $this->createUser();

        $this->actingAs($user);

        $data = [
            "name" => $this->faker->name(),
            "project_id" => 1,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
        ];

        $response = $this->postJson('/api/project_session/{2}', $data);
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => 'Sorry! A valid project session is required to perform this action.',
            ]);
    }

    public function testCannotUpdateProjectSessionWhenNotAuthorized()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_id" => $creator->id,
            "addedby_type" => $creator::class,
        ]);
        $projectSession = ProjectSession::factory()->create([
            "project_id" => $project->id,
            "user_id" => $creator->id,
        ]);

        $this->actingAs($user);

        $data = [
            "name" => $this->faker->name(),
            "project_id" => 1,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
        ];

        $response = $this->postJson("/api/project_session/{$projectSession->id}", $data);
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => "Sorry! You are not authorized to perform this action on the project session with {$projectSession->name} name.",
            ]);
    }

    public function testCannotUpdateProjectSessionWhenStartDateComesBeforeToday()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_id" => $user->id,
            "addedby_type" => $user::class,
        ]);
        $projectSession = ProjectSession::factory()->create([
            "project_id" => $project->id,
            "user_id" => $user->id,
        ]);

        $this->actingAs($user);

        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => now()->subDay()->toDateTimeString(),
        ];

        $response = $this->postJson("/api/project_session/{$projectSession->id}", $data);
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => "Sorry! The start date for the session should be on or after today.",
            ]);
    }

    public function testCannotUpdateProjectSessionWhenStartDateComesBeforeProjectStartDate()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_id" => $user->id,
            "addedby_type" => $user::class,
            "start_date" => $startDate = now()->addDays(2),
            "end_date" => $endDate = now()->addYears(2),
        ]);
        $projectSession = ProjectSession::factory()->create([
            "project_id" => $project->id,
            "user_id" => $user->id,
        ]);

        $this->actingAs($user);

        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => $startDate->subDay()->toDateTimeString(),
            "end_date" => $endDate->subDay()->toDateTimeString(),
        ];

        $startDate->addDay();
        $endDate->addDay();

        $response = $this->postJson("/api/project_session/{$projectSession->id}", $data);
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => "Sorry! The start date for the session should come after or on {$startDate->toDateTimeString()} date.",
            ]);
    }

    public function testCannotUpdateProjectSessionWhenEndDateComesBeforeProjectEndDate()
    {
        $user = $this->createUser();
        $project = Project::factory()->create([
            "addedby_id" => $user->id,
            "addedby_type" => $user::class,
            "start_date" => $startDate = now()->addDays(2),
            "end_date" => $endDate = now()->addYears(2),
        ]);
        $projectSession = ProjectSession::factory()->create([
            "project_id" => $project->id,
            "user_id" => $user->id,
        ]);

        $this->actingAs($user);

        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => $startDate->addDay()->toDateTimeString(),
            "end_date" => $endDate->addDay()->toDateTimeString(),
        ];

        $startDate->subDay();
        $endDate->subDay();

        $response = $this->postJson("/api/project_session/{$projectSession->id}", $data);
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => "Sorry! The end date for the session should come before or on {$endDate->toDateTimeString()} date.",
            ]);
    }

    public function testCannotUpdateProjectSessionWhenEndDateComesBeforeStartDate()
    {
        $user = $this->createUser();
        $project = Project::factory()->create([
            "addedby_id" => $user->id,
            "addedby_type" => $user::class,
            "start_date" => $startDate = now()->addDays(2),
            "end_date" => $endDate = now()->addYears(2),
        ]);
        $projectSession = ProjectSession::factory()->create([
            "project_id" => $project->id,
            "user_id" => $user->id,
        ]);

        $this->actingAs($user);

        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => $startDate->addDay()->toDateTimeString(),
            "end_date" => $startDate->subDay()->toDateTimeString(),
        ];

        $response = $this->postJson("/api/project_session/{$projectSession->id}", $data);
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => "Sorry! The end date for the session should be either the same day as or after the start date.",
            ]);
    }

    public function testCannotUpdateProjectSessionWhenEndTimeIsNotAtLeastAnHourAfterStartTime()
    {
        $user = $this->createUser();
        $project = Project::factory()->create([
            "addedby_id" => $user->id,
            "addedby_type" => $user::class,
            "start_date" => $startDate = now()->addDays(2),
            "end_date" => $endDate = now()->addYears(2),
        ]);
        $projectSession = ProjectSession::factory()->create([
            "project_id" => $project->id,
            "user_id" => $user->id,
        ]);

        $this->actingAs($user);

        $startTime = now()->addHour();
        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => $startDate->addDay()->toDateTimeString(),
            "end_date" => $endDate->subDay()->toDateTimeString(),
            "start_time" => $startTime->toTimeString(),
            "end_time" => $startTime->subHour()->addMinutes(30)->toTimeString(),
        ];

        $response = $this->postJson("/api/project_session/{$projectSession->id}", $data);
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => "Sorry! The end time for the session should be at least an hour after the start time.",
            ]);
    }

    public function testCanUpdateProjectSessionWhenCreator()
    {
        $user = $this->createUser();
        $project = Project::factory()->create([
            "addedby_id" => $user->id,
            "addedby_type" => $user::class,
            "start_date" => $startDate = now()->addDays(2),
            "end_date" => $endDate = now()->addYears(2),
        ]);
        $projectSession = ProjectSession::factory()->create([
            "project_id" => $project->id,
            "user_id" => $user->id,
        ]);

        $this->actingAs($user);

        $startTime = now()->addHour();
        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => $startDate->addDay()->toDateTimeString(),
            "end_date" => $endDate->subDay()->toDateTimeString(),
            "start_time" => $startTime->toTimeString(),
            "end_time" => $startTime->addMinutes(60)->addMinutes(30)->toTimeString(),
        ];

        $response = $this->postJson("/api/project_session/{$projectSession->id}", $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "projectSession" => [
                    "name" => $data["name"],
                    "startDate" => Carbon::parse($data["start_date"])->diffForHumans(),
                    "endDate" => Carbon::parse($data["end_date"])->diffForHumans(),
                    "startTime" => Carbon::parse($data["start_time"])->diffForHumans(),
                    "endTime" => Carbon::parse($data["end_time"])->diffForHumans(),
                ],
            ]);

        $this->assertDatabaseHas("project_sessions", [
            "project_id" => $project->id,
            "user_id" => $user->id,
            "name" => $data["name"],
        ]);
    }

    public function testCanUpdateProjectSessionWhenFacilitatorOfProject()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_id" => $creator->id,
            "addedby_type" => $creator::class,
            "start_date" => $startDate = now()->addDays(2),
            "end_date" => $endDate = now()->addYears(2),
        ]);
        $projectSession = ProjectSession::factory()->create([
            "project_id" => $project->id,
            "user_id" => $creator->id,
        ]);

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($user);
        $participation->save();

        $this->actingAs($user);

        $startTime = now()->addHour();
        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => $startDate->addDay()->toDateTimeString(),
            "end_date" => $endDate->subDay()->toDateTimeString(),
            "start_time" => $startTime->toTimeString(),
            "end_time" => $startTime->addMinutes(60)->addMinutes(30)->toTimeString(),
        ];

        $response = $this->postJson("/api/project_session/{$projectSession->id}", $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "projectSession" => [
                    "name" => $data["name"],
                    "startDate" => Carbon::parse($data["start_date"])->diffForHumans(),
                    "endDate" => Carbon::parse($data["end_date"])->diffForHumans(),
                    "startTime" => Carbon::parse($data["start_time"])->diffForHumans(),
                    "endTime" => Carbon::parse($data["end_time"])->diffForHumans(),
                ],
            ]);

        $this->assertDatabaseHas("project_sessions", [
            "project_id" => $project->id,
            "user_id" => $creator->id,
            "name" => $data["name"],
        ]);
    }

    public function testCanUpdateProjectSessionWhenAdmin()
    {
        $admin = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_id" => $creator->id,
            "addedby_type" => $creator::class,
            "start_date" => $startDate = now()->addDays(2),
            "end_date" => $endDate = now()->addYears(2),
        ]);
        $projectSession = ProjectSession::factory()->create([
            "project_id" => $project->id,
            "user_id" => $creator->id,
        ]);

        $userType = UserType::factory()->create([
            "name" => UserTypeEnum::admin->value
        ]);
        $admin->userTypes()->attach($userType->id);

        $this->actingAs($admin);

        $startTime = now()->addHour();
        $data = [
            "name" => $this->faker->name(),
            "project_id" => $project->id,
            "day_of_week" => 2,
            "type" => ProjectSessionTypeEnum::online->value,
            "period" => ProjectSessionPeriodEnum::weekly->value,
            "start_date" => $startDate->addDay()->toDateTimeString(),
            "end_date" => $endDate->subDay()->toDateTimeString(),
            "start_time" => $startTime->toTimeString(),
            "end_time" => $startTime->addMinutes(60)->addMinutes(30)->toTimeString(),
        ];

        $response = $this->postJson("/api/project_session/{$projectSession->id}", $data);
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "projectSession" => [
                    "name" => $data["name"],
                    "startDate" => Carbon::parse($data["start_date"])->diffForHumans(),
                    "endDate" => Carbon::parse($data["end_date"])->diffForHumans(),
                    "startTime" => Carbon::parse($data["start_time"])->diffForHumans(),
                    "endTime" => Carbon::parse($data["end_time"])->diffForHumans(),
                ],
            ]);

        $this->assertDatabaseHas("project_sessions", [
            "project_id" => $project->id,
            "user_id" => $creator->id,
            "name" => $data["name"],
        ]);
    }

    public function testCannotDeleteProjectSessionWithInvalidProjectSessionId()
    {
        $user = $this->createUser();

        $this->actingAs($user);

        $response = $this->deleteJson('/api/project_session/{2}');
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => 'Sorry! A valid project session is required to perform this action.',
            ]);
    }

    public function testCannotDeleteProjectSessionWhenNotAuthorized()
    {
        // a different facilitator can update but cannot delete
        $user = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_id" => $creator->id,
            "addedby_type" => $creator::class,
        ]);
        $projectSession = ProjectSession::factory()->create([
            "project_id" => $project->id,
            "user_id" => $creator->id,
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson("/api/project_session/{$projectSession->id}");
        
        $response->assertStatus(422)
            ->assertJson([
                "message" => "Sorry! You are not authorized to perform this action on the project session with {$projectSession->name} name.",
            ]);
    }

    public function testCanDeleteProjectSessionWhenCreator()
    {
        $user = $this->createUser();
        $project = Project::factory()->create([
            "addedby_id" => $user->id,
            "addedby_type" => $user::class,
        ]);
        $projectSession = ProjectSession::factory()->create([
            "project_id" => $project->id,
            "user_id" => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson("/api/project_session/{$projectSession->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
            ]);

        $this->assertDatabaseMissing("project_sessions", [
            "id" => $projectSession->id,
            "project_id" => $project->id,
            "user_id" => $user->id,
        ]);
    }

    public function testCanDeleteProjectSessionWhenOfficialOfProject()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $official = $this->createUser();
        $company = Company::factory()->create([
            "user_id" => $creator->id
        ]);
        $project = Project::factory()->create([
            "addedby_id" => $company->id,
            "addedby_type" => $company::class,
        ]);
        $relation = $company->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($official);
        $relation->save();

        $projectSession = ProjectSession::factory()->create([
            "project_id" => $project->id,
            "user_id" => $creator->id,
        ]);

        $this->actingAs($official);

        $response = $this->deleteJson("/api/project_session/{$projectSession->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
            ]);

        $this->assertDatabaseMissing("project_sessions", [
            "project_id" => $project->id,
            "user_id" => $creator->id,
            "name" => $projectSession->name,
        ]);
    }

    public function testCanDeleteProjectSessionWhenAdmin()
    {
        $admin = $this->createUser();
        $creator = $this->createUser();
        $project = Project::factory()->create([
            "addedby_id" => $creator->id,
            "addedby_type" => $creator::class,
        ]);
        $projectSession = ProjectSession::factory()->create([
            "project_id" => $project->id,
            "user_id" => $creator->id,
        ]);

        $userType = UserType::factory()->create([
            "name" => UserTypeEnum::admin->value
        ]);
        $admin->userTypes()->attach($userType->id);

        $this->assertDatabaseHas("project_sessions", [
            "project_id" => $project->id,
            "user_id" => $creator->id,
            "name" => $projectSession->name,
        ]);

        $this->actingAs($admin);

        $response = $this->deleteJson("/api/project_session/{$projectSession->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
            ]);

        $this->assertDatabaseMissing("project_sessions", [
            "project_id" => $project->id,
            "user_id" => $creator->id,
            "name" => $projectSession->name,
        ]);
    }
}
