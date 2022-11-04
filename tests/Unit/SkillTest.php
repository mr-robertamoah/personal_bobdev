<?php

namespace Tests\Unit;

use App\DTOs\JobDTO;
use App\DTOs\SkillDTO;
use App\DTOs\SkillTypeDTO;
use App\Exceptions\SkillException;
use App\Models\SkillType;
use App\Models\User;
use App\Models\UserType;
use App\Services\JobService;
use App\Services\SkillService;
use App\Services\SkillTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateSkillWithoutAnAddedByUser()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
        
        (new SkillService)->createSkill(
            SkillDTO::new()
        );
    }

    public function testCannotCreateSkillIfNotAnAuthorizedUserType()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! You are not authorized to create a skill.');

        $user = User::factory()->create();
        
        (new SkillService)->createSkill(
            SkillDTO::new()->fromArray(['user' => $user, 'name' => 'hey'])
        );
    }

    public function testCannotCreateSkillWithoutASKillTypeId()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! A valid skill type is required to perform this action.');

        $user = User::factory()->has(UserType::factory(['name' => UserType::FACILITATOR]))->create();
        
        (new SkillService)->createSkill(
            SkillDTO::new()->fromArray(['user' => $user, 'name' => 'hey'])
        );
    }

    public function testCannotCreateSkillWithoutAName()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage("Sorry! The name and description of the skill and user's id are required.");

        $user = User::factory()->has(UserType::factory(['name' => UserType::FACILITATOR]))->create();
        
        SkillType::factory()->create(['name' => "Programming Language", 'user_id' => $user->id]);
        
        (new SkillService)->createSkill(
            SkillDTO::new()->fromArray(['user' => $user, 'description' => 'hey', 'skillTypeId' => 1])
        );
    }

    public function testCannotCreateSkillWithoutADescription()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage("Sorry! The name and description of the skill and user's id are required.");

        $user = User::factory()->has(UserType::factory(['name' => UserType::FACILITATOR]))->create();
        
        SkillType::factory()->create(['name' => "Programming Language", 'user_id' => $user->id]);
        
        (new SkillService)->createSkill(
            SkillDTO::new()->fromArray(['user' => $user, 'name' => 'hey', 'skillTypeId' => 1])
        );
    }

    public function testCanCreateSkillAsFacilitator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );
        
        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
            'description' => 'coding for making programs and software'
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
            'skill_type_id' => $skillType->id,
            'user_id' => $user->id,
        ]);
    }

    public function testCanCreateSkillAsAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );
        
        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
            'description' => 'coding for making programs and software'
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
            'skill_type_id' => $skillType->id,
            'user_id' => $user->id,
        ]);
    }

    public function testCannotUpdateSkillWithoutAnAddedByUser()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
        
        (new SkillService)->updateSkill(
            SkillDTO::new()
        );
    }

    public function testCannotUpdateSkillIfNotAnAuthorizedUserType()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! You are not authorized to update a skill.');

        $user = User::factory()->create();
        
        (new SkillService)->updateSkill(
            SkillDTO::new()->fromArray(['user' => $user, 'name' => 'hey'])
        );
    }

    public function testCannotUpdateSkillWithoutANameAndDescription()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage("Sorry! The name and description of the skill and user's id are required.");

        $user = User::factory()->has(UserType::factory(['name' => UserType::FACILITATOR]))->create();
        
        SkillType::factory()->create(['name' => "Programming Language", 'user_id' => $user->id]);
        
        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'user' => $user, 
                'name' => 'hey', 
                'description' => 'sup', 
                'skillTypeId' => 1
            ])
        );

        (new SkillService)->updateSkill(
            SkillDTO::new()->fromArray([
                'user' => $user, 
                'name' => '', 
                'description' => '', 
                'skillId' => $skill->id
            ])
        );
    }

    public function testCannotUpdateSkillWithoutAValidSKillId()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! A valid skill is required to perform this action.');

        $user = User::factory()->has(UserType::factory(['name' => UserType::ADMIN]))->create();
        
        $skillType = SkillType::factory()->create(['name' => "Programming Language", 'user_id' => $user->id]);
        
        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'user' => $user, 
                'name' => 'hey',  
                'description' => 'sup',  
                'skillTypeId' => $skillType->id
            ])
        );

        (new SkillService)->updateSkill(
            SkillDTO::new()->fromArray([
                'user' => $user, 
                'name' => 'hey',  
            ])
        );
    }

    public function testCannotUpdateSkillWithoutBeingCreatorOrAdmin()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! You are not authorized to update a skill.');

        $user = User::factory()->has(UserType::factory(['name' => UserType::FACILITATOR]))->create();
        $user2 = User::factory()->has(UserType::factory(['name' => UserType::FACILITATOR]))->create();
        
        $skillType = SkillType::factory()->create(['name' => "Programming Language", 'user_id' => $user2->id]);
        
        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'user' => $user, 
                'name' => 'hey', 
                'description' => 'sup', 
                'skillTypeId' => $skillType->id
            ])
        );
        
        (new SkillService)->updateSkill(
            SkillDTO::new()->fromArray([
                'user' => $user2, 
                'name' => 'hey', 
                'description' => 'sup', 
                'skillId' => $skill->id
            ])
        );
    }

    public function testCanUpdateNameOfSkillAsCreator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );
        
        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
            'description' => 'coding for making programs and software'
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
            'skill_type_id' => $skillType->id,
            'user_id' => $user->id,
        ]);
        
        $Updatedskill = (new SkillService)->updateSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP8',
                'user' => $user,
                'skillId' => $skill->id
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP8',
            'description' => 'backend language for web sites',
            'skill_type_id' => $skillType->id,
            'user_id' => $user->id,
        ]);

        $this->assertNotEquals($Updatedskill->name, $skill->name);
        $this->assertEquals($Updatedskill->description, $skill->description);
    }

    public function testCanUpdateNameOfSkillAsAdminIfNotCreator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        $admin = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );
        
        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
            'description' => 'coding for making programs and software'
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
            'skill_type_id' => $skillType->id,
            'user_id' => $user->id,
        ]);
        
        $Updatedskill = (new SkillService)->updateSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP8',
                'user' => $admin,
                'skillId' => $skill->id
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP8',
            'description' => 'backend language for web sites',
            'skill_type_id' => $skillType->id,
            'user_id' => $user->id,
        ]);

        $this->assertNotEquals($Updatedskill->name, $skill->name);
        $this->assertEquals($Updatedskill->description, $skill->description);
    }

    public function testCanUpdateDescriptionOfSkillAsCreator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );
        
        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
            'description' => 'coding for making programs and software'
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
            'skill_type_id' => $skillType->id,
            'user_id' => $user->id,
        ]);
        
        $Updatedskill = (new SkillService)->updateSkill(
            SkillDTO::new()->fromArray([
                'description' => 'backend language for the web.',
                'user' => $user,
                'skillId' => $skill->id
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'backend language for the web.',
            'skill_type_id' => $skillType->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals($Updatedskill->name, $skill->name);
        $this->assertNotEquals($Updatedskill->description, $skill->description);
    }

    public function testCannotDeleteSkillWithoutAnAddedByUser()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
        
        (new SkillService)->deleteSkill(
            SkillDTO::new()
        );
    }

    public function testCannotDeleteSkillIfNotAnAuthorizedUserType()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! You are not authorized to delete a skill.');

        $user = User::factory()->create();
        
        (new SkillService)->deleteSkill(
            SkillDTO::new()->fromArray(['user' => $user, 'name' => 'hey'])
        );
    }

    public function testCannotDeleteSkillWithoutAValidSKillId()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! A valid skill is required to perform this action.');

        $user = User::factory()->has(UserType::factory(['name' => UserType::ADMIN]))->create();
        
        $skillType = SkillType::factory()->create(['name' => "Programming Language", 'user_id' => $user->id]);
        
        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'user' => $user, 
                'name' => 'hey',  
                'description' => 'sup',  
                'skillTypeId' => $skillType->id
            ])
        );

        (new SkillService)->deleteSkill(
            SkillDTO::new()->fromArray([
                'user' => $user, 
                'name' => 'hey',  
            ])
        );
    }

    public function testCannotDeleteSkillWithoutBeingCreatorOrAdmin()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! You are not authorized to delete a skill.');

        $user = User::factory()->has(UserType::factory(['name' => UserType::FACILITATOR]))->create();
        $user2 = User::factory()->has(UserType::factory(['name' => UserType::FACILITATOR]))->create();
        
        $skillType = SkillType::factory()->create(['name' => "Programming Language", 'user_id' => $user2->id]);
        
        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'user' => $user, 
                'name' => 'hey', 
                'description' => 'sup', 
                'skillTypeId' => $skillType->id
            ])
        );
        
        (new SkillService)->deleteSkill(
            SkillDTO::new()->fromArray([
                'user' => $user2, 
                'name' => 'hey', 
                'description' => 'sup', 
                'skillId' => $skill->id
            ])
        );
    }

    public function testCanDeleteSkillAsAdminIfNotCreatorAndNotAttachedToOtherJobUsers()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        $admin = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );
        
        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
            'description' => 'coding for making programs and software'
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
            'skill_type_id' => $skillType->id,
            'user_id' => $user->id,
        ]);
        
        $result = (new SkillService)->deleteSkill(
            SkillDTO::new()->fromArray([
                'user' => $admin,
                'skillId' => $skill->id
            ])
        );

        $this->assertDatabaseMissing('skills', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
            'skill_type_id' => $skillType->id,
            'user_id' => $user->id,
        ]);
        
        $this->assertTrue((bool)$result);
    }

    public function testCanDeleteSkillAsAdminIfNotCreatorAndAttachedToOtherJobUsers()
    {
        $user1 = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        $user2 = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        $admin = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user1,
            ])
        );
        
        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );
        
        $job1 = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'create solutions that make web apps function',
                'addedBy' => $user1,
            ])
        );

        $jobUser1 = $job1->jobUsers()->create(['user_id' => $user1->id]);
        $jobUser1->jobUserSkills()->create(['skill_id' => $skill->id]);
        
        $job2 = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Designer',
                'description' => 'i make front end of websites cool',
                'addedBy' => $user2,
            ])
        );

        $jobUser2= $job2->jobUsers()->create(['user_id' => $user2->id]);
        $jobUser2->jobUserSkills()->create(['skill_id' => $skill->id]);

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
            'description' => 'coding for making programs and software'
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Developer',
            'description' => 'create solutions that make web apps function',
        ]);

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Designer',
            'description' => 'i make front end of websites cool',
        ]);

        $this->assertDatabaseHas('job_user', [
            'user_id' => $user1->id,
            'job_id' => $job1->id,
        ]);

        $this->assertDatabaseHas('job_user', [
            'user_id' => $user2->id,
            'job_id' => $job2->id,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser1->id,
            'skill_id' => $skill->id,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser2->id,
            'skill_id' => $skill->id,
        ]);
        
        $result = (new SkillService)->deleteSkill(
            SkillDTO::new()->fromArray([
                'user' => $admin,
                'skillId' => $skill->id
            ])
        );

        $this->assertDatabaseMissing('skills', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
            'skill_type_id' => $skillType->id,
            'user_id' => $user1->id,
        ]);

        $this->assertDatabaseMissing('job_user_skill', [
            'job_user_id' => $jobUser1->id,
            'skill_id' => $skill->id,
        ]);

        $this->assertDatabaseMissing('job_user_skill', [
            'job_user_id' => $jobUser2->id,
            'skill_id' => $skill->id,
        ]);
        
        $this->assertTrue((bool)$result);
    }

    public function testCanDeleteSkillAsCreatorIfNotAttachedToOtherJobUsers()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );
        
        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
            'description' => 'coding for making programs and software'
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
            'skill_type_id' => $skillType->id,
            'user_id' => $user->id,
        ]);
        
        $result = (new SkillService)->deleteSkill(
            SkillDTO::new()->fromArray([
                'user' => $user,
                'skillId' => $skill->id
            ])
        );

        $this->assertDatabaseMissing('skills', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
            'skill_type_id' => $skillType->id,
            'user_id' => $user->id,
        ]);
        
        $this->assertTrue((bool)$result);
    }

    public function testCannotDeleteSkillAsCreatorIfNotAdminAndAttachedToOtherJobUsers()
    {
        $user1 = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        $user2 = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        $user3 = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user1,
            ])
        );
        
        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );
        
        $job1 = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'description' => 'create solutions that make web apps function',
                'addedBy' => $user1,
            ])
        );

        $jobUser1 = $job1->jobUsers()->create(['user_id' => $user1->id]);
        $jobUser1->jobUserSkills()->create(['skill_id' => $skill->id]);
        
        $job2 = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Designer',
                'description' => 'i make front end of websites cool',
                'addedBy' => $user2,
            ])
        );

        $jobUser2= $job2->jobUsers()->create(['user_id' => $user2->id]);
        $jobUser2->jobUserSkills()->create(['skill_id' => $skill->id]);
        
        $job3 = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Full stack developer',
                'description' => 'involved in all aspects of creating and serving web apps',
                'addedBy' => $user3,
            ])
        );

        $jobUser3= $job3->jobUsers()->create(['user_id' => $user3->id]);
        $jobUser3->jobUserSkills()->create(['skill_id' => $skill->id]);

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
            'description' => 'coding for making programs and software'
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Developer',
            'description' => 'create solutions that make web apps function',
        ]);

        $this->assertDatabaseHas('jobs', [
            'name' => 'Web Designer',
            'description' => 'i make front end of websites cool',
        ]);

        $this->assertDatabaseHas('jobs', [
            'name' => 'Full stack developer',
            'description' => 'involved in all aspects of creating and serving web apps',
        ]);

        $this->assertDatabaseHas('job_user', [
            'user_id' => $user1->id,
            'job_id' => $job1->id,
        ]);

        $this->assertDatabaseHas('job_user', [
            'user_id' => $user2->id,
            'job_id' => $job2->id,
        ]);

        $this->assertDatabaseHas('job_user', [
            'user_id' => $user3->id,
            'job_id' => $job3->id,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser1->id,
            'skill_id' => $skill->id,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser2->id,
            'skill_id' => $skill->id,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser3->id,
            'skill_id' => $skill->id,
        ]);
        
        $result = (new SkillService)->deleteSkill(
            SkillDTO::new()->fromArray([
                'user' => $user1,
                'skillId' => $skill->id
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
            'skill_type_id' => $skillType->id,
            'user_id' => $user1->id,
        ]);

        $this->assertDatabaseMissing('job_user_skill', [
            'job_user_id' => $jobUser1->id,
            'skill_id' => $skill->id,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser2->id,
            'skill_id' => $skill->id,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser3->id,
            'skill_id' => $skill->id,
        ]);
        
        $this->assertTrue((bool)$result);
    }

    public function testCannotDeleteSkillBasedOnSkillTypeIfNotAnAdminAndCreator()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! You do not have permission to perform this action.');

        $user1 = User::factory()
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
                'addedBy' => $user1
            ])
        );

        $skill1 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for making backend of web sites',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $skill2 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Vue',
                'description' => 'framework for the front end solutions',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);

        $result = (new SkillService)->deleteSkillsBasedOnSkillType(
            SkillDTO::new()->fromArray([
                'user' => $user2,
                'skillType' => $skillType
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);
    }

    public function testCannotDeleteSkillBasedOnSkillTypeWithoutAUser()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! You do not have permission to perform this action.');

        $user1 = User::factory()
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
                'addedBy' => $user1
            ])
        );

        $skill1 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for making backend of web sites',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $skill2 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Vue',
                'description' => 'framework for the front end solutions',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);

        $result = (new SkillService)->deleteSkillsBasedOnSkillType(
            SkillDTO::new()->fromArray([
                'skillType' => $skillType
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);
    }

    public function testCannotDeleteSkillBasedOnAnInvalidSkillType()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! A skill type is required to perform this action');

        $user1 = User::factory()
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
                'addedBy' => $user1
            ])
        );

        $skill1 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for making backend of web sites',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $skill2 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Vue',
                'description' => 'framework for the front end solutions',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);

        $result = (new SkillService)->deleteSkillsBasedOnSkillType(
            SkillDTO::new()->fromArray([
                'user' => $user2,
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);
    }

    public function testCanDeleteSkillBasedOnSkillTypeIfAnAdmin()
    {
        $user1 = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $user2 = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'addedBy' => $user1
            ])
        );

        $skill1 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for making backend of web sites',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $skill2 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Vue',
                'description' => 'framework for the front end solutions',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);

        $result = (new SkillService)->deleteSkillsBasedOnSkillType(
            SkillDTO::new()->fromArray([
                'user' => $user2,
                'skillType' => $skillType
            ])
        );

        $this->assertDatabaseMissing('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseMissing('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);

        $this->assertTrue($result);
    }

    public function testCannotDeleteSkillBasedOnSkillTypeAndUserIfNotAFacilitator()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! You have to be a facilitator to perform this action.');

        $user1 = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $user2 = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::DONOR
            ]), [], 'userTypes')
            ->create();

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'addedBy' => $user1
            ])
        );

        $skill1 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for making backend of web sites',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $skill2 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Vue',
                'description' => 'framework for the front end solutions',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);

        $result = (new SkillService)->deleteSkillsBasedOnSkillTypeAndUser(
            SkillDTO::new()->fromArray([
                'user' => $user2,
                'skillType' => $skillType
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);
    }

    public function testCannotDeleteSkillBasedOnSkillTypeAndUserWithoutAUser()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! A skill type and user are required to perform this action');

        $user1 = User::factory()
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
                'addedBy' => $user1
            ])
        );

        $skill1 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for making backend of web sites',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $skill2 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Vue',
                'description' => 'framework for the front end solutions',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);

        $result = (new SkillService)->deleteSkillsBasedOnSkillTypeAndUser(
            SkillDTO::new()->fromArray([
                'skillType' => $skillType
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);
    }

    public function testCannotDeleteSkillBasedOnSkillTypeAndUserWithoutAValidSkillType()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! A skill type and user are required to perform this action');

        $user1 = User::factory()
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
                'addedBy' => $user1
            ])
        );

        $skill1 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for making backend of web sites',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $skill2 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Vue',
                'description' => 'framework for the front end solutions',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);

        $result = (new SkillService)->deleteSkillsBasedOnSkillTypeAndUser(
            SkillDTO::new()->fromArray([
                'user' => $user2
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);
    }

    public function testCannotDeleteSkillBasedOnSkillTypeAndUserIfAnAdmin()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage('Sorry! You have to be a facilitator to perform this action.');

        $user1 = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $user2 = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'addedBy' => $user1
            ])
        );

        $skill1 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for making backend of web sites',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $skill2 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Vue',
                'description' => 'framework for the front end solutions',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);

        $result = (new SkillService)->deleteSkillsBasedOnSkillTypeAndUser(
            SkillDTO::new()->fromArray([
                'user' => $user2,
                'skillType' => $skillType
            ])
        );

        $this->assertDatabaseMissing('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseMissing('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);

        $this->assertTrue($result);
    }

    public function testCanDeleteSkillBasedOnSkillTypeAndUserIfAFaciliator()
    {
        $user1 = User::factory()
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
                'addedBy' => $user1
            ])
        );

        $skill1 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for making backend of web sites',
                'user' => $user1,
                'skillTypeId' => $skillType->id
            ])
        );

        $skill2 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Vue',
                'description' => 'framework for the front end solutions',
                'user' => $user2,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);

        $result = (new SkillService)->deleteSkillsBasedOnSkillTypeAndUser(
            SkillDTO::new()->fromArray([
                'user' => $user2,
                'skillType' => $skillType
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for making backend of web sites',
        ]);

        $this->assertDatabaseMissing('skills', [
            'name' => 'Vue',
            'description' => 'framework for the front end solutions',
        ]);

        $this->assertTrue($result);
    }
}
