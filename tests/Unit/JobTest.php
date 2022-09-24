<?php

namespace Tests\Unit;

use App\DTOs\JobDTO;
use App\Exceptions\JobException;
use App\Models\Job;
use App\Models\User;
use App\Models\UserType;
use App\Services\JobService;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateJobWithoutAnAddedByUser()
    {
        $this->expectException(JobException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
        
        (new JobService)->createJob(
            JobDTO::new()
        );
    }

    public function testCannotCreateJobIfNotAnAuthorizedUserType()
    {
        $this->expectException(JobException::class);
        $this->expectExceptionMessage('Sorry! You are not authorized to create a job.');

        $user = User::factory()->create();
        
        (new JobService)->createJob(
            JobDTO::new()->fromArray(['addedBy' => $user, 'name' => 'hey'])
        );
    }

    public function testCannotCreateJobWithoutJobName()
    {
        $this->expectException(JobException::class);
        $this->expectExceptionMessage('Sorry! The name of the job is required.');

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        (new JobService)->createJob(
            JobDTO::new()->fromArray(['addedBy' => $user])
        );
    }

    public function testCanCreateJobIfAFacilitator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => $user
            ])
        );

        $this
            ->assertDatabaseHas('jobs', [
            'name' => 'Web Developer',
            'description' => 'i create backend solutions that enhance websites'
        ])
            ->assertEquals($job->name, 'Web Developer');
        $this->assertEquals($job->user_id, $user->id);

    }

    public function testCanCreateJobIfASuperAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => $user
            ])
        );

        $this
            ->assertDatabaseHas('jobs', [
            'name' => 'Web Developer',
            'description' => 'i create backend solutions that enhance websites'
        ])
            ->assertEquals($job->name, 'Web Developer');
        $this->assertEquals($job->user_id, $user->id);

    }

    public function testCanCreateJobIfAnAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
        
        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => $user
            ])
        );

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Developer',
            'description' => 'i create backend solutions that enhance websites'
        ])
            ->assertEquals($job->name, 'Web Developer');
        $this->assertEquals($job->user_id, $user->id);

    }

    public function testCannotUpdateJobWithoutAnAddedByUser()
    {
        $this->expectException(JobException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
        
        (new JobService)->updateJob(
            JobDTO::new()
        );
    }

    public function testThrowExceptionIfJobIdIsNotProvidedWhenUpdatingJob()
    {
        $this->expectException(JobException::class);
        $this->expectExceptionMessage('Sorry! A job id is required for this operation.');

        $user = User::factory()->create();
        
        (new JobService)->updateJob(
            JobDTO::new()->fromArray(['addedBy' => $user])
        );

    }

    public function testCannotUpdateAJobWhichWasNotFoundInDatabase()
    {
        $this->expectException(JobException::class);
        $this->expectExceptionMessage("Sorry! The Job with id 1 was not found.");

        $user = User::factory()->create();
        
        (new JobService)->updateJob(
            JobDTO::new()->fromArray(['addedBy' => $user, 'jobId' => 1])
        );

    }

    public function testCanUpdateAJobIfCreator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => $user
            ])
        );
        
        $result = (new JobService)->updateJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Designer',
                'addedBy' => $user,
                'jobId' => $job->id
            ])
        );

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Designer',
            'description' => 'i create backend solutions that enhance websites'
        ]);

        $this->assertTrue((bool) $result);

    }

    public function testCanUpdateAJobIfAnAdminAndNotTheCreator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
        
        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => User::factory()
                ->hasAttached(UserType::factory([
                    'name' => UserType::FACILITATOR
                ]), [], 'userTypes')
                ->create()
            ])
        );
        
        $result = (new JobService)->updateJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Designer',
                'addedBy' => $user,
                'jobId' => $job->id
            ])
        );

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Designer',
            'description' => 'i create backend solutions that enhance websites'
        ]);

        $this->assertTrue((bool) $result);
        
    }

    public function testCanUpdateAJobIfASuperAdminAndNotTheCreator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => User::factory()
                ->hasAttached(UserType::factory([
                    'name' => UserType::FACILITATOR
                ]), [], 'userTypes')
                ->create()
            ])
        );
        
        $result = (new JobService)->updateJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Designer',
                'addedBy' => $user,
                'jobId' => $job->id
            ])
        );

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Designer',
            'description' => 'i create backend solutions that enhance websites'
        ]);

        $this->assertTrue((bool) $result);
        
    }

    public function testCannotDeleteJobWithoutAnAddedByUser()
    {
        $this->expectException(JobException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
        
        (new JobService)->deleteJob(
            JobDTO::new()
        );
    }

    public function testThrowExceptionIfJobIdIsNotProvidedWhenDeletingJob()
    {
        $this->expectException(JobException::class);
        $this->expectExceptionMessage('Sorry! A job id is required for this operation.');

        $user = User::factory()->create();
        
        (new JobService)->deleteJob(
            JobDTO::new()->fromArray(['addedBy' => $user])
        );

    }

    public function testCannotDeleteAJobWhichWasNotFoundInDatabase()
    {
        $this->expectException(JobException::class);
        $this->expectExceptionMessage("Sorry! The Job with id 1 was not found.");

        $user = User::factory()->create();
        
        (new JobService)->deleteJob(
            JobDTO::new()->fromArray(['addedBy' => $user, 'jobId' => 1])
        );

    }

    public function testCanDeleteAJobIfCreatorAndOnlyOneAttachedToJob()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $job = (new JobService)->createJob(
            $jobDTO = JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => $user
            ])
        );

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Developer',
            'description' => 'i create backend solutions that enhance websites'
        ]);

        (new JobService)->attachJobToUser(
            $jobDTO->fromArray([
                'job' => $job,
                'attachedTo' => $user
            ])
        );

        $this->assertDatabaseHas('job_user', [
            'user_id' => $user->id,
            'job_id' => $job->id
        ]);
        
        $result = (new JobService)->deleteJob(
            JobDTO::new()->fromArray([
                'addedBy' => $user,
                'jobId' => $job->id
            ])
        );

        $this->assertSoftDeleted('jobs', [
            'name' => 'Web Developer',
            'description' => 'i create backend solutions that enhance websites'
        ]);

        $this->assertDatabaseMissing('job_user', [
            'user_id' => $user->id,
            'job_id' => $job->id
        ]);

        $this->assertTrue((bool) $result);

    }

    public function testCannotDeleteAJobIfCreatorAndWhenMoreThanOneAttachedToJob()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => $user
            ])
        );
        
        $user2 = User::factory()
        ->hasAttached(UserType::factory([
            'name' => UserType::FACILITATOR
        ]), [], 'userTypes')
        ->create();

        $user2->jobUsers()->create(['job_id' => $job->id]);
        
        $result = (new JobService)->deleteJob(
            JobDTO::new()->fromArray([
                'addedBy' => $user,
                'jobId' => $job->id
            ])
        );

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Developer',
            'description' => 'i create backend solutions that enhance websites'
        ]);

        $this->assertDatabaseHas('job_user', [
            'user_id' => $user2->id,
            'job_id' => $job->id
        ]);

        $this->assertTrue((bool) $result);

    }

    public function testCanDeleteAJobIfAnAdminAndNotTheCreator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
        
        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => User::factory()
                ->hasAttached(UserType::factory([
                    'name' => UserType::FACILITATOR
                ]), [], 'userTypes')
                ->create()
            ])
        );
        
        $result = (new JobService)->deleteJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Designer',
                'addedBy' => $user,
                'jobId' => $job->id
            ])
        );

        $this->assertDatabaseMissing('jobs', [
            'name' => 'Web Designer',
            'description' => 'i create backend solutions that enhance websites'
        ]);

        $this->assertTrue((bool) $result);
        
    }

    public function testCanDeleteAJobIfASuperAdminAndNotTheCreator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => User::factory()
                ->hasAttached(UserType::factory([
                    'name' => UserType::FACILITATOR
                ]), [], 'userTypes')
                ->create()
            ])
        );
        
        $result = (new JobService)->deleteJob(
            JobDTO::new()->fromArray([
                'addedBy' => $user,
                'jobId' => $job->id
            ])
        );
        
        $this->assertSoftDeleted('jobs', [
            'name' => 'Web Developer',
            'description' => 'i create backend solutions that enhance websites'
        ]);

        $this->assertTrue((bool) $result);
        
    }

    public function testCanGetJobWithId()
    {
        Job::factory(['name'=>'Web Developer', 'user_id'=>1])->create();

        $job = (new JobService)->getJob(
            JobDTO::new()->fromArray(['jobId'=>1])
        );

        $this->assertDatabaseHas('jobs', ['name'=>'Web Developer']);
        $this->assertEquals($job->name, 'Web Developer');
    }

    public function testCanGetJobWithName()
    {
        Job::factory(['name'=>'Web Developer', 'user_id'=>1])->create();

        $job = (new JobService)->getJob(
            JobDTO::new()->fromArray(['name'=> 'Web Developer'])
        );

        $this->assertDatabaseHas('jobs', ['name'=>'Web Developer']);
        $this->assertEquals($job->name, 'Web Developer');
    }

    public function testCanGetNullJobWithoutIdOrName()
    {
        Job::factory(['name'=>'Web Developer', 'user_id'=>1])->create();

        $job = (new JobService)->getJob(
            JobDTO::new()->fromArray(['jobId'=>1])
        );

        $this->assertDatabaseHas('jobs', ['name'=>'Web Developer']);
        $this->assertEquals($job->name, 'Web Developer');
    }

    public function testCanGetJobsWithSimilarName()
    {
        Job::factory()->count(2)->state(new Sequence(
            ['name'=>'Web Developer', 'user_id'=>1],
            ['name'=>'Web Designer', 'user_id'=>1],
        ))->create();

        $jobs = (new JobService)->getJobs(
            JobDTO::new()->fromArray(['name'=>'Web'])
        );
        
        $this->assertDatabaseHas('jobs', ['name'=>'Web Developer']);
        $this->assertDatabaseHas('jobs', ['name'=>'Web Designer']);
        $this->assertEquals('Web Developer', $jobs->toArray()['data'][0]['name']);
        $this->assertEquals('Web Designer', $jobs->toArray()['data'][1]['name']);
    }

    public function testCanDetachJobFromUsers()
    {
        $job = Job::factory(['name'=> 'Web Developer', 'user_id' => 1])->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $job->jobUsers()->create(['user_id' => $user1->id]);
        $job->jobUsers()->create(['user_id' => $user2->id]);
        
        $this->assertNotNull($user1->jobUsers()->where('job_id', $job->id)->first());
        $this->assertNotNull($user2->jobUsers()->where('job_id', $job->id)->first());

        (new JobService)->detachJobFromUsers(
            JobDTO::new()->fromArray([
                'job' => $job
            ])
        );

        $this->assertNull($user1->jobUsers()->where('job_id', $job->id)->first());
        $this->assertNull($user2->jobUsers()->where('job_id', $job->id)->first());
    }
}
