<?php

namespace Tests\Feature;

use App\DTOs\JobDTO;
use App\Exceptions\JobException;
use App\Models\Job;
use App\Models\User;
use App\Models\UserType;
use App\Services\JobService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class JobTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreateJobAsAnAdminWithoutAttaching()
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

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $attachedto = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $attachedto->userTypes()->attach($userType->id);
        
        $this->actingAs($user);

        $response = $this->post("/api/job/create", [
            'name' => 'Web Developer',
        ]);
        
        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'job' => [
                    'name' => 'Web Developer'
                ]
            ]);

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Developer'
        ]);

        $this->assertDatabaseMissing('job_user', [
            'job_id' => $response->json('job')['id'],
            'user_id' => $attachedto->id,
        ]);
    }

    public function testCanCreateJobAsAnAdminAndAttachToFacilitator()
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

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $attachedto = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $attachedto->userTypes()->attach($userType->id);
        
        $this->actingAs($user);

        $response = $this->post("/api/job/create", [
            'name' => 'Web Developer',
            'user_id' => $attachedto->id,
            'attach' => true
        ]);
        
        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'job' => [
                    'name' => 'Web Developer'
                ]
            ]);

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Developer'
        ]);

        $this->assertDatabaseHas('job_user', [
            'job_id' => $response->json('job')['id'],
            'user_id' => $attachedto->id,
        ]);
    }

    public function testCanCreateJobAsAnFacilitatorAndAttach()
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

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $facilitator = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $facilitator->userTypes()->attach($userType->id);
        
        $this->actingAs($facilitator);

        $response = $this->post("/api/job/create", [
            'name' => 'Web Developer',
            'user_id' => $facilitator->id,
            'attach' => true
        ]);
        
        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'job' => [
                    'name' => 'Web Developer'
                ]
            ]);

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Developer'
        ]);

        $this->assertDatabaseHas('job_user', [
            'job_id' => $response->json('job')['id'],
            'user_id' => $facilitator->id,
        ]);
    }
    
    public function testCanDeleteJobAsAnAdminAndDetachFromAllUsers()
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

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $facilitator1 = User::factory()->has(UserType::factory([
            'name' => UserType::FACILITATOR, 
            'user_id' => $user->id
        ]))->create();
        $facilitator2 = User::factory()->has(UserType::factory([
            'name' => UserType::FACILITATOR, 
            'user_id' => $user->id
        ]))->create();
        
        $job = $user->addedJobs()->create(['name' => 'Web Developer']);

        $facilitator1->jobUsers()->create(['job_id' => $job->id]);
        $facilitator2->jobUsers()->create(['job_id' => $job->id]);

        $this->assertNotNull($facilitator1->jobUsers()->where('job_id', $job->id)->first());
        $this->assertNotNull($facilitator2->jobUsers()->where('job_id', $job->id)->first());
        
        $this->actingAs($user);

        $response = $this->delete("/api/job/{$job->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Developer'
        ]);

        $this->assertNull($facilitator1->jobUsers()->where('job_id', $job->id)->first());
        $this->assertNull($facilitator2->jobUsers()->where('job_id', $job->id)->first());
    }
    
    public function testCanDeleteJobAsAFacilitatorAndDetachIfOnlyAttchedToOneUser()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType->id);
        
        $job = $user->addedJobs()->create(['name' => 'Web Developer']);

        $user->jobUsers()->create(['job_id' => $job->id]);

        $this->assertNotNull($user->jobUsers()->where('job_id', $job->id)->first());
        
        $this->actingAs($user);

        $response = $this->delete("/api/job/{$job->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);

        $this->assertSoftDeleted('jobs', [
            'name' => 'Web Developer'
        ]);

        $this->assertNull($user->jobUsers()->where('job_id', $job->id)->first());
    }
    
    public function testCanOnlyDetachJobAsAFacilitatorIfCreatorAndNotTheOnlyOneAttachedToJob()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $facilitator1 = User::factory()->has(UserType::factory([
            'name' => UserType::FACILITATOR, 
            'user_id' => $user->id
        ]))->create();
        $facilitator2 = User::factory()->has(UserType::factory([
            'name' => UserType::FACILITATOR, 
            'user_id' => $user->id
        ]))->create();
        
        $job = $user->addedJobs()->create(['name' => 'Web Developer']);

        $user->jobUsers()->create(['job_id' => $job->id]);
        $facilitator1->jobUsers()->create(['job_id' => $job->id]);
        $facilitator2->jobUsers()->create(['job_id' => $job->id]);

        $this->assertNotNull($user->jobUsers()->where('job_id', $job->id)->first());
        $this->assertNotNull($facilitator1->jobUsers()->where('job_id', $job->id)->first());
        $this->assertNotNull($facilitator2->jobUsers()->where('job_id', $job->id)->first());
        
        $this->actingAs($user);

        $response = $this->delete("/api/job/{$job->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Developer'
        ]);

        $this->assertNull($user->jobUsers()->where('job_id', $job->id)->first());
        $this->assertNotNull($facilitator1->jobUsers()->where('job_id', $job->id)->first());
        $this->assertNotNull($facilitator2->jobUsers()->where('job_id', $job->id)->first());
    }
    
    public function testCanUpdateJobAsAFacilitator()
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

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);
        
        $facilitator1 = User::factory()->has(UserType::factory([
            'name' => UserType::FACILITATOR, 
            'user_id' => $user->id
        ]))->create();
        $facilitator2 = User::factory()->has(UserType::factory([
            'name' => UserType::FACILITATOR, 
            'user_id' => $user->id
        ]))->create();
        
        $job = $user->addedJobs()->create(['name' => 'Web Developer']);

        $facilitator1->jobUsers()->create(['job_id' => $job->id]);
        $facilitator2->jobUsers()->create(['job_id' => $job->id]);

        $this->assertNotNull($facilitator1->jobUsers()->where('job_id', $job->id)->first());
        $this->assertNotNull($facilitator2->jobUsers()->where('job_id', $job->id)->first());
        
        $this->actingAs($user);

        $response = $this->delete("/api/job/{$job->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Developer'
        ]);

        $this->assertNull($facilitator1->jobUsers()->where('job_id', $job->id)->first());
        $this->assertNull($facilitator2->jobUsers()->where('job_id', $job->id)->first());
    }

    public function testCanGetJobWithJobId()
    {
        $admin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'addedBy' => $admin
            ])
        );

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Developer',
        ]);

        $this->actingAs($user);

        $response = $this->get("/api/job?id={$job->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'job' => [
                    'name' => 'Web Developer',
                ]
            ]);
    }

    public function testCanGetJobWithJobName()
    {
        $admin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'addedBy' => $admin
            ])
        );

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Developer',
        ]);

        $this->actingAs($user);

        $response = $this->get("/api/job?name={$job->name}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'job' => [
                    'name' => 'Web Developer',
                ]
            ]);
    }

    public function testCanGetJobsWithJobName()
    {
        $admin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'addedBy' => $admin
            ])
        );

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Developer',
        ]);

        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Native Developer',
                'addedBy' => $admin
            ])
        );

        $this->assertDatabaseHas('jobs', [
            'name' => 'Native Developer',
        ]);

        $this->actingAs($user);

        $response = $this->get("/api/jobs?name=developer");

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['name' => 'Web Developer'],
                    ['name' => 'Native Developer'],
                ]
            ]);
    }

    public function testCannotAttachJobToAnotherUserIfNotAdmin()
    {
        // $this->expectException(JobException::class);
        // $this->expectExceptionMessage('Sorry! You must be an admin if you are attaching job to a different account. The user you are trying to attach the job to must be a facilitator.');

        $admin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $user2 = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah2@yahoo.com",
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType->id);
        $user2->userTypes()->attach($userType->id);

        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Develop',
                'addedBy' => $admin
            ])
        );

        $this->actingAs($user);
        
        $response = $this->postJson("/api/job/{$job->id}/attach", [
            'user_id' => $user2->id
        ]);

        $response
            ->assertStatus(500);

        $this->assertDatabaseMissing('job_user', [
            'job_id' => $job->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('job_user', [
            'job_id' => $job->id,
            'user_id' => $user2->id,
        ]);
    }

    public function testCanAttachJobToFacilitator()
    {
        $admin = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $user = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $userType = $admin->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType->id);

        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Develop',
                'addedBy' => $admin
            ])
        );

        $this->actingAs($user);
        
        $response = $this->postJson("/api/job/{$job->id}/attach");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);

        $this->assertDatabaseHas('job_user', [
            'job_id' => $job->id,
            'user_id' => $user->id,
        ]);
    }
}
