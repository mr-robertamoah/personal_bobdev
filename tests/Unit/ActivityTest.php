<?php

namespace Tests\Unit;

use App\DTOs\ActivityDTO;
use App\Exceptions\ActivityException;
use App\Models\Activity;
use App\Models\Job;
use App\Models\User;
use App\Models\UserType;
use App\Services\ActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateActivityWithoutAppropriateData()
    {
        $this->expectException(ActivityException::class);
        $this->expectExceptionMessage('Sorry! You do not have the needed information to perform this action.');

        $user = User::factory()->create();
        $job = Job::factory()->create(['user_id' => $user->id]);

        (new ActivityService)->createActivity(
            ActivityDTO::new()->fromArray([
                'performedby' => $user,
                'performedon' => $job,
            ])
        );

        $this->assertDatabaseMissing('activities', [
            'performedby_type' => $user::class,
            'performedby_id' => $user->id,
            'performedon_type' => $job::class,
            'performedon_id' => $job->id,
            'action' => 'create',
        ]);   
    }

    public function testCanCreateActivityWithoutAppropriateData()
    {
        $user = User::factory()->create();
        $job = Job::factory()->create(['user_id' => $user->id]);

        (new ActivityService)->createActivity(
            ActivityDTO::new()->fromArray([
                'performedby' => $user,
                'performedon' => $job,
                'action' => 'create'
            ])
        );

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $user::class,
            'performedby_id' => $user->id,
            'performedon_type' => $job::class,
            'performedon_id' => $job->id,
            'action' => 'create',
        ]);
    }

    public function testCannotDeleteAnActivityWithoutId()
    {
        $this->expectException(ActivityException::class);
        $this->expectExceptionMessage("Sorry! The id of the activity is required to perform this action.");

        (new ActivityService)->deleteActivity(
            User::factory()->create(),
            null
        );
    }

    public function testCannotDeleteAnActivityIfNotAnAdmin()
    {
        $this->expectException(ActivityException::class);
        $this->expectExceptionMessage("Sorry! You cannot delete the activity with id 1.");
        
        $activity = Activity::factory()
            ->has(User::factory(), 'performedby')
            ->has(Job::factory(['user_id' => 1]), 'performedon')
            ->create();
        (new ActivityService)->deleteActivity(
            User::factory()->create(),
            $activity->id
        );
    }

    public function testCanDeleteAnActivityIfAnAdmin()
    {        
        $user = User::factory()->create();
        $job = Job::factory()->create(['user_id' => $user->id]);

        $activity = (new ActivityService)->createActivity(
            ActivityDTO::new()->fromArray([
                'performedby' => $user,
                'performedon' => $job,
                'action' => 'create'
            ])
        );

        (new ActivityService)->deleteActivity(
            User::factory()->has(UserType::factory(['name' => UserType::ADMIN]))->create(),
            $activity->id
        );

        $this->assertDatabaseHas('activities', [
            'performedby_type' => $user::class,
            'performedby_id' => $user->id,
            'performedon_type' => $job::class,
            'performedon_id' => $job->id,
            'action' => 'create',
        ]);

        $this->assertNotNull($activity->refresh()->deleted_at);
    }
}
