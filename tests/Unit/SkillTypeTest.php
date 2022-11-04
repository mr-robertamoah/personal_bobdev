<?php

namespace Tests\Unit;

use App\DTOs\JobDTO;
use App\DTOs\SkillDTO;
use App\DTOs\SkillTypeDTO;
use App\Exceptions\SkillTypeException;
use App\Models\SkillType;
use App\Models\User;
use App\Models\UserType;
use App\Services\JobService;
use App\Services\SkillService;
use App\Services\SkillTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillTypeTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateSkillTypeWithoutAnAddedByUser()
    {
        $this->expectException(SkillTypeException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
        
        (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()
        );
    }

    public function testCannotCreateSkillTypeIfNotAnAuthorizedUserType()
    {
        $this->expectException(SkillTypeException::class);
        $this->expectExceptionMessage('Sorry! You are not authorized to create a skill type.');

        $user = User::factory()->create();
        
        (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray(['addedBy' => $user, 'name' => 'hey'])
        );
    }

    public function testCannotCreateSkillTypeWithoutJobName()
    {
        $this->expectException(SkillTypeException::class);
        $this->expectExceptionMessage('Sorry! The name of the skill type is required.');

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray(['addedBy' => $user])
        );
    }

    public function testCanCreateSkillTypeIfAFacilitator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => $user
            ])
        );

        $this
            ->assertDatabaseHas('skill_types', [
            'name' => 'Web Developer',
            'description' => 'i create backend solutions that enhance websites'
        ])
            ->assertEquals($skillType->name, 'Web Developer');
        $this->assertEquals($skillType->user_id, $user->id);

    }

    public function testCanCreateSkillTypeIfASuperAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => $user
            ])
        );

        $this
            ->assertDatabaseHas('skill_types', [
            'name' => 'Web Developer',
            'description' => 'i create backend solutions that enhance websites'
        ])
            ->assertEquals($skillType->name, 'Web Developer');
        $this->assertEquals($skillType->user_id, $user->id);

    }

    public function testCanCreateSkillTypeIfAnAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => $user
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Web Developer',
            'description' => 'i create backend solutions that enhance websites'
        ])
            ->assertEquals($skillType->name, 'Web Developer');
        $this->assertEquals($skillType->user_id, $user->id);

    }

    public function testCannotUpdateSkillTypeWithoutAnAddedByUser()
    {
        $this->expectException(SkillTypeException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
        
        (new SkillTypeService)->updateSkillType(
            SkillTypeDTO::new()
        );
    }

    public function testThrowExceptionIfSkillTypeIdIsNotProvidedWhenUpdatingJob()
    {
        $this->expectException(SkillTypeException::class);
        $this->expectExceptionMessage('Sorry! A skill type id is required for this operation.');

        $user = User::factory()->create();
        
        (new SkillTypeService)->updateSkillType(
            SkillTypeDTO::new()->fromArray(['addedBy' => $user])
        );

    }

    public function testCannotUpdateASkillTypeWhichWasNotFoundInDatabase()
    {
        $this->expectException(SkillTypeException::class);
        $this->expectExceptionMessage("Sorry! The skill type with id 1 was not found.");

        $user = User::factory()->create();
        
        (new SkillTypeService)->updateSkillType(
            SkillTypeDTO::new()->fromArray(['addedBy' => $user, 'skillTypeId' => 1])
        );

    }

    public function testCanUpdateASkillTypeIfCreator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => $user
            ])
        );
        
        $result = (new SKillTypeService)->updateSKillType(
            SKillTypeDTO::new()->fromArray([
                'name' => 'Web Designer',
                'addedBy' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Web Designer',
            'description' => 'i create backend solutions that enhance websites'
        ]);

        $this->assertTrue((bool) $result);

    }

    public function testCanUpdateASkillTypeIfAnAdminAndNotTheCreator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => User::factory()
                ->hasAttached(UserType::factory([
                    'name' => UserType::FACILITATOR
                ]), [], 'userTypes')
                ->create()
            ])
        );
        
        $result = (new SkillTypeService)->updateSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Web Designer',
                'addedBy' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Web Designer',
            'description' => 'i create backend solutions that enhance websites'
        ]);

        $this->assertTrue((bool) $result);
        
    }

    public function testCanUpdateASkillTypeIfASuperAdminAndNotTheCreator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create backend solutions that enhance websites',
                'addedBy' => User::factory()
                ->hasAttached(UserType::factory([
                    'name' => UserType::FACILITATOR
                ]), [], 'userTypes')
                ->create()
            ])
        );
        
        $result = (new SkillTypeService)->updateSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Web Designer',
                'addedBy' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Web Designer',
            'description' => 'i create backend solutions that enhance websites'
        ]);

        $this->assertTrue((bool) $result);
        
    }

    public function testCannotDeleteSkillTypeWithoutAnAddedByUser()
    {
        $this->expectException(SkillTypeException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
        
        (new SkillTypeService)->deleteSkillType(
            SkillTypeDTO::new()
        );
    }

    public function testThrowExceptionIfSkillTypeIdIsNotProvidedWhenDeletingSkillType()
    {
        $this->expectException(SkillTypeException::class);
        $this->expectExceptionMessage('Sorry! A skill type id is required for this operation.');

        $user = User::factory()->create();
        
        (new SkillTypeService)->deleteSkillType(
            SkillTypeDTO::new()->fromArray(['addedBy' => $user])
        );

    }

    public function testCannotDeleteASkillTypeWhichWasNotFoundInDatabase()
    {
        $this->expectException(SkillTypeException::class);
        $this->expectExceptionMessage("Sorry! The skill type with id 1 was not found.");

        $user = User::factory()->create();
        
        (new SkillTypeService)->deleteSkillType(
            SkillTypeDTO::new()->fromArray(['addedBy' => $user, 'skillTypeId' => 1])
        );

    }

    public function testCannotDeleteASkillTypeIfNotAuthorized()
    {
        $skillType = User::factory()->create()->skillTypes()->create([
            'name' => 'Programming Language',
            'description' => 'to help create web and native apps'
        ]);

        $this->expectException(SkillTypeException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to delete the skill type with name {$skillType->name}.");

        $user = User::factory()->create();
        
        (new SkillTypeService)->deleteSkillType(
            SkillTypeDTO::new()->fromArray(['addedBy' => $user, 'skillTypeId' => $skillType->id])
        );

    }

    public function testCanDeleteASkillTypeAndSkillsIfCreatorAndNoSkillByOtherUsersIsAttached()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'how to code programs and software.',
                'addedBy' => $user
            ])
        );
        
        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create web sites.',
                'addedBy' => $user
            ])
        );
        
        $skill1 =(new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'a programming language for the web.',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );
        
        $skill2 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Dart',
                'description' => 'a programming language for the web or native.',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $jobUser = $job->jobUsers()->create(['user_id' => $user->id]);

        $jobUser->jobUserSkills()->create(['skill_id' => $skill1->id]);
        $jobUser->jobUserSkills()->create(['skill_id' => $skill2->id]);

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
            'description' => 'how to code programs and software.',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Dart',
            'description' => 'a programming language for the web or native.',
            'skill_type_id' => $skillType->id
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'a programming language for the web.',
            'skill_type_id' => $skillType->id
        ]);

        $this->assertDatabaseHas('job_user', [
            'job_id' => $job->id,
            'user_id' => $user->id
        ]);
        
        $result = (new SkillTypeService)->deleteSkillType(
            SkillTypeDTO::new()->fromArray([
                'addedBy' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertSoftDeleted('skill_types', [
            'name' => 'Programming Language',
            'description' => 'how to code programs and software.',
        ]);

        $this->assertDatabaseMissing('skills', [
            'name' => 'Dart',
            'description' => 'a programming language for the web or native.',
            'skill_type_id' => $skillType->id
        ]);

        $this->assertDatabaseMissing('skills', [
            'name' => 'PHP',
            'description' => 'a programming language for the web.',
            'skill_type_id' => $skillType->id
        ]);

        $this->assertTrue((bool) $result);

    }

    public function testCanOnlyDeleteSkillsAndNotSkillTypeIfCreatorAndThereAreSkillsOfOtherUsersAttached()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        $user2 = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'how to code programs and software.',
                'addedBy' => $user
            ])
        );
        
        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create web sites.',
                'addedBy' => $user
            ])
        );
        
        $skill1 =(new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'a programming language for the web.',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );
        
        $skill2 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Dart',
                'description' => 'a programming language for the web or native.',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $jobUser = $job->jobUsers()->create(['user_id' => $user->id]);
        
        $jobUser->jobUserSkills()->create(['skill_id' => $skill1->id]);
        $jobUser->jobUserSkills()->create(['skill_id' => $skill2->id]);
        
        $job2 = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create web sites.',
                'addedBy' => $user2
            ])
        );
        
        $skill3 =(new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Vue',
                'description' => 'a programming framework for web front end.',
                'user' => $user2,
                'skillTypeId' => $skillType->id
            ])
        );

        $jobUser2 = $job2->jobUsers()->create(['user_id' => $user2->id]);
        
        $jobUser2->jobUserSkills()->create(['skill_id' => $skill3->id]);

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
            'description' => 'how to code programs and software.',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Dart',
            'description' => 'a programming language for the web or native.',
            'skill_type_id' => $skillType->id
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'a programming language for the web.',
            'skill_type_id' => $skillType->id
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'a programming framework for web front end.',
            'skill_type_id' => $skillType->id
        ]);

        $this->assertDatabaseHas('job_user', [
            'job_id' => $job->id,
            'user_id' => $user->id
        ]);

        $this->assertDatabaseHas('job_user', [
            'job_id' => $job2->id,
            'user_id' => $user2->id
        ]);
        
        $result = (new SkillTypeService)->deleteSkillType(
            SkillTypeDTO::new()->fromArray([
                'addedBy' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
            'description' => 'how to code programs and software.',
        ]);

        $this->assertDatabaseMissing('skills', [
            'name' => 'Dart',
            'description' => 'a programming language for the web or native.',
            'skill_type_id' => $skillType->id
        ]);

        $this->assertDatabaseMissing('skills', [
            'name' => 'PHP',
            'description' => 'a programming language for the web.',
            'skill_type_id' => $skillType->id
        ]);

        $this->assertDatabaseHas('skills', [
                'name' => 'Vue',
                'description' => 'a programming framework for web front end.',
                'skill_type_id' => $skillType->id
            ]);

        $this->assertTrue((bool) $result);

    }

    public function testCanDeleteSkillTypeAndAllSkillsIfAdminEvenIfThereAreSkillsOfOtherUsersAttached()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
        $user2 = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'how to code programs and software.',
                'addedBy' => $user
            ])
        );
        
        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create web sites.',
                'addedBy' => $user
            ])
        );
        
        $skill1 =(new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'a programming language for the web.',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );
        
        $skill2 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Dart',
                'description' => 'a programming language for the web or native.',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $jobUser = $job->jobUsers()->create(['user_id' => $user->id]);
        
        $jobUser->jobUserSkills()->create(['skill_id' => $skill1->id]);
        $jobUser->jobUserSkills()->create(['skill_id' => $skill2->id]);
        
        $job2 = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'i create web sites.',
                'addedBy' => $user2
            ])
        );
        
        $skill3 =(new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Vue',
                'description' => 'a programming framework for web front end.',
                'user' => $user2,
                'skillTypeId' => $skillType->id
            ])
        );

        $jobUser2 = $job2->jobUsers()->create(['user_id' => $user2->id]);
        
        $jobUser2->jobUserSkills()->create(['skill_id' => $skill3->id]);

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
            'description' => 'how to code programs and software.',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Dart',
            'description' => 'a programming language for the web or native.',
            'skill_type_id' => $skillType->id
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'a programming language for the web.',
            'skill_type_id' => $skillType->id
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'a programming framework for web front end.',
            'skill_type_id' => $skillType->id
        ]);

        $this->assertDatabaseHas('job_user', [
            'job_id' => $job->id,
            'user_id' => $user->id
        ]);
        
        $result = (new SkillTypeService)->deleteSkillType(
            SkillTypeDTO::new()->fromArray([
                'addedBy' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertSoftDeleted('skill_types', [
            'name' => 'Programming Language',
            'description' => 'how to code programs and software.',
        ]);

        $this->assertDatabaseMissing('skills', [
            'name' => 'Dart',
            'description' => 'a programming language for the web or native.',
            'skill_type_id' => $skillType->id
        ]);

        $this->assertDatabaseMissing('skills', [
            'name' => 'PHP',
            'description' => 'a programming language for the web.',
            'skill_type_id' => $skillType->id
        ]);

        $this->assertDatabaseMissing('skills', [
                'name' => 'Vue',
                'description' => 'a programming framework for web front end.',
                'skill_type_id' => $skillType->id
            ]);

        $this->assertTrue((bool) $result);

    }
}
