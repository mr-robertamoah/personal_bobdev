<?php

namespace Tests\Unit;

use App\DTOs\ProjectDTO;
use App\DTOs\SkillDTO;
use App\DTOs\SkillTypeDTO;
use App\Enums\ProjectParticipantEnum;
use App\Enums\RequestTypeEnum;
use App\Exceptions\ProjectException;
use App\Exceptions\SkillException;
use App\Exceptions\UserException;
use App\Models\User;
use App\Models\UserType;
use App\Services\ProjectService;
use App\Services\SkillService;
use App\Services\SkillTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testCannotCreateProjectWithoutAnAddedByUser()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
        
        (new ProjectService)->createProject(
            ProjectDTO::new()
        );
    }

    public function testCannotCreateProjectIfNotAuthorized()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage('Sorry! You are not authorized to create a project.');

        $user = User::factory()->create();
        
        (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray(['addedby' => $user, 'name' => 'hey'])
        );
    }

    public function testCannotCreateProjectWithoutAName()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage("Sorry! The name and description of the project are required.");

        $user = User::factory()->has(UserType::factory(['name' => UserType::FACILITATOR]))->create();
        
        (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray(['addedby' => $user, 'description' => 'hey',])
        );
    }

    public function testCannotCreateProjectWithoutADescription()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage("Sorry! The name and description of the project are required.");

        $user = User::factory()->has(UserType::factory(['name' => UserType::FACILITATOR]))->create();
        
        (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray(['addedby' => $user, 'name' => 'hey'])
        );
    }

    public function testCanCreateProjectAsAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
        
        $date = now();
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
                'startDate' => $date->toDateTimeString()
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
            'start_date' => $date->toDateTimeString(),
            'end_date' => null
        ]);
    }

    public function testCanCreateProjectAsFacilitator()
    {
        $user = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
    }

    public function testCanCreateProjectAsStudent()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
    }

    public function testCannotUpdateProjectWithoutAddedby()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
        
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        $project = (new ProjectService)->updateProject(
            ProjectDTO::new()->fromArray([
                'name' => 'Dart',
            ])
        );

        $this->assertDatabaseMissing('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
    }

    public function testCannotUpdateProjectWithoutId()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage('Sorry! A valid project is required to perform this action.');

        $user = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        $project = (new ProjectService)->updateProject(
            ProjectDTO::new()->fromArray([
                'name' => 'Dart',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseMissing('projects', [
            'name' => 'Dart',
            'description' => 'backend language for web sites',
        ]);
    }

    public function testCannotUpdateProjectIfNotOwnerOrAdmin()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to update a project.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        $updater = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        $project = (new ProjectService)->updateProject(
            ProjectDTO::new()->fromArray([
                'name' => 'Dart',
                'addedby' => $updater,
                'projectId' => $project->id
            ])
        );

        $this->assertDatabaseMissing('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
    }

    public function testCanUpdateProjectIfAdminAndNotOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        $updater = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        $project = (new ProjectService)->updateProject(
            ProjectDTO::new()->fromArray([
                'name' => 'Dart',
                'addedby' => $updater,
                'projectId' => $project->id
            ])
        );

        $this->assertDatabaseMissing('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Dart',
            'description' => 'backend language for web sites',
        ]);
    }

    public function testCanUpdateProjectIfOwner()
    {
        $user = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        $project = (new ProjectService)->updateProject(
            ProjectDTO::new()->fromArray([
                'name' => 'Dart',
                'addedby' => $user,
                'projectId' => $project->id
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'Dart',
            'description' => 'backend language for web sites',
        ]);

        $this->assertDatabaseMissing('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
    }

    public function testCanUpdateProjectDatesIfOwner()
    {
        $user = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
            'start_date' => null,
            'end_date' => null,
        ]);
        
        $date = now();
        $project = (new ProjectService)->updateProjectDates(
            ProjectDTO::new()->fromArray([
                'startDate' => $date->toDateTimeString(),
                'endDate' => $date->addYear(1)->toDateTimeString(),
                'addedby' => $user,
                'projectId' => $project->id
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
            'start_date' => $date->subYear(1)->toDateTimeString(),
            'end_date' => $date->addYear(1)->toDateTimeString(),
        ]);
    }

    public function testCannotDeleteProjectWithoutBeingOwnerOrAdmin()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
        
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        $project = (new ProjectService)->deleteProject(
            ProjectDTO::new()
        );
    }

    public function testCannotDeleteProjectWithoutId()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage('Sorry! A valid project is required to perform this action.');

        $user = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        $project = (new ProjectService)->deleteProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
    }

    public function testCanDeleteProjectWhenAddedby()
    {
        $user = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        $project = (new ProjectService)->deleteProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $user,
                'projectId' => $project->id,
            ])
        );

        $this->assertSoftDeleted('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
    }

    public function testCannotAttachInvalidSkillToProjectWhenAddedby()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage("Sorry, (1, 2) ids do not point to valid skills.");

        $user = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);

        (new ProjectService)->addSkillsToProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $user,
                'projectId' => $project->id
            ]), [1,2]
        );
    }

    public function testCanAttachValidSkillToProjectWhenAddedby()
    {
        $user = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);

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

        (new ProjectService)->addSkillsToProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $user,
                'projectId' => $project->id
            ]), [$skill->id]
        );

        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skill->id,
        ]);
    }

    public function testCanAttachValidSkillToProjectWhenFacilitatorForProject()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        $facilitator = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($facilitator);
        $participation->save();

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

        (new ProjectService)->addSkillsToProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $facilitator,
                'projectId' => $project->id
            ]), [$skill->id]
        );

        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skill->id,
        ]);
    }

    public function testCanAttachValidSkillToProjectWhenAdmin()
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
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);

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

        (new ProjectService)->addSkillsToProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $admin,
                'projectId' => $project->id
            ]), [$skill->id]
        );

        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skill->id,
        ]);
    }

    public function testCannotDetachInvalidSkillToProjectWhenAddedby()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage("Sorry, (1, 2) ids do not point to valid skills.");

        $user = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);

        (new ProjectService)->removeSkillsFromProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $user,
                'projectId' => $project->id
            ]), [1,2]
        );
    }

    public function testCanDetachValidSkillToProjectWhenAddedby()
    {
        $user = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);

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

        (new ProjectService)->addSkillsToProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $user,
                'projectId' => $project->id
            ]), [$skill->id]
        );

        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skill->id,
        ]);

        (new ProjectService)->removeSkillsFromProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $user,
                'projectId' => $project->id
            ]), [$skill->id]
        );

        $this->assertDatabaseMissing('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skill->id,
        ]);
    }

    public function testCanDetachValidSkillToProjectWhenFacilitatorForProject()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        $facilitator = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($facilitator);
        $participation->save();

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

        (new ProjectService)->addSkillsToProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $user,
                'projectId' => $project->id
            ]), [$skill->id]
        );

        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skill->id,
        ]);

        (new ProjectService)->removeSkillsFromProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $facilitator,
                'projectId' => $project->id
            ]), [$skill->id]
        );

        $this->assertDatabaseMissing('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skill->id,
        ]);
    }

    public function testCanDetachValidSkillToProjectWhenAdmin()
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
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);

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

        (new ProjectService)->addSkillsToProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $user,
                'projectId' => $project->id
            ]), [$skill->id]
        );

        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skill->id,
        ]);

        (new ProjectService)->removeSkillsFromProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $admin,
                'projectId' => $project->id
            ]), [$skill->id]
        );

        $this->assertDatabaseMissing('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skill->id,
        ]);
    }

    public function testCannotSendParticipantRequestForProjectWhenNotAuthorized()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to update a project.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();
            
        $otherUser = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $facilitator = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        (new ProjectService)->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'projectId' => $project->id,
                'participationType' => 'facilitator',
                'participantId' => $facilitator->id,
                'addedby' => $otherUser,
            ])
        );
    }

    public function testCannotSendParticipantRequestForProjectWithoutProjectId()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage('Sorry! A valid project is required to perform this action.');

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $facilitator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        (new ProjectService)->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'participationType' => 'facilitator',
                'participantId' => $facilitator->id,
                'addedby' => $user,
            ])
        );
    }
    
    public function testCannotSendParticipantRequestForProjectWithInvalidParticipationArray()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage("Sorry! The users and their respective participation type must be specified.");

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
        
        $facilitator = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        (new ProjectService)->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'projectId' => $project->id,
                'participationType' => 'facilitator',
                'addedby' => $admin,
            ])
        );
    }
    
    public function testCannotSendParticipantRequestForProjectWithoutParticipants()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage("Sorry! The users and their respective participation type must be specified.");

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
        
        $facilitator = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        (new ProjectService)->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'projectId' => $project->id,
                'participations' => [],
                'addedby' => $admin,
            ])
        );
    }

    public function testCannotSendParticipantRequestForProjectWithInvalidParticipant()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage("Sorry, no participant was provided.");

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
        
        $facilitator = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        (new ProjectService)->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'projectId' => $project->id,
                'participations' => [
                    "200" => 'facilitator'],
                'addedby' => $admin,
            ])
        );
    }

    public function testCannotSendParticipantRequestForProjectWithInvalidParticipantType()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage("Sorry, participant cannot participate in the project as HEY.");

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
        
        $facilitator = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        (new ProjectService)->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'projectId' => $project->id,
                'participations' => [
                    $facilitator->id => 'hey',
                ],
                'addedby' => $admin,
            ])
        );
    }

    public function testCannotSendParticipantRequestToPotentialFacilitatorWithoutFacilitatorUserTypeForProject()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage("Sorry, participant is not a facilitator");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $facilitator = User::factory()->create();
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        (new ProjectService)->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'projectId' => $project->id,
                'participations' => [
                    $facilitator->id => 'facilitator'],
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $project->id,
            'participant_id' => $facilitator->id,
            'participant_type' => $facilitator::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value,
        ]);
    }

    public function testCannotSendParticipantRequestToPotentialLearnerWithoutLearnerUserTypeForProject()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage("Sorry, participant is not a learner");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $facilitator = User::factory()->create();
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        (new ProjectService)->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'projectId' => $project->id,
                'participations' => [$facilitator->id => 'learner'],
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $project->id,
            'participant_id' => $facilitator->id,
            'participant_type' => $facilitator::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value,
        ]);
    }

    public function testCannotSendParticipatingRequestToAParticipantOfTheProject()
    {
        $this->expectException(ProjectException::class);
        $this->expectExceptionMessage("Sorry! User is already participating in this project.");

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $facilitator = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($facilitator);
        $participation->save();

        $this->assertDatabaseHas('project_participant', [
            'project_id' => $project->id,
            'participant_id' => $facilitator->id,
            'participant_type' => $facilitator::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value,
        ]);
        
        (new ProjectService)->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'projectId' => $project->id,
                'participations' => [$facilitator->id => 'learner'],
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $project->id,
            'participant_id' => $facilitator->id,
            'participant_type' => $facilitator::class,
            'participating_as' => ProjectParticipantEnum::learner->value,
        ]);

        $this->assertDatabaseMissing('requests', [
            'from_id' => $user->id,
            'from_type' => $user->id,
            'for_id' => $project->id,
            'for_type' => $project::class,
            'to_id' => $facilitator->id,
            'to_type' => $facilitator::class
        ]);
    }

    public function testCanSendParticipationRequestToUserThatIsAlreadyParticipatingInProjectAsSponsor()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $facilitator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::DONOR
            ]), [], 'userTypes')
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::sponsor->value
        ]);
        $participation->participant()->associate($facilitator);
        $participation->save();

        $this->assertDatabaseHas('project_participant', [
            'project_id' => $project->id,
            'participant_id' => $facilitator->id,
            'participant_type' => $facilitator::class,
            'participating_as' => ProjectParticipantEnum::sponsor->value,
        ]);

        (new ProjectService)->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'projectId' => $project->id,
                'participations' => [$facilitator->id => 'learner'],
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $project->id,
            'participant_id' => $facilitator->id,
            'participant_type' => $facilitator::class,
            'participating_as' => ProjectParticipantEnum::learner->value,
        ]);

        $this->assertDatabaseHas('requests', [
            'from_id' => $user->id,
            'from_type' => $user::class,
            'for_id' => $project->id,
            'for_type' => $project::class,
            'to_id' => $facilitator->id,
            'to_type' => $facilitator::class
        ]);

        $this->assertDatabaseHas('activities', [
            'performedby_id' => $user->id,
            'performedby_type' => $user::class,
            'performedon_id' => $project->id,
            'performedon_type' => $project::class,
            'action' => "sendParticipationRequests",
        ]);
    }

    public function testCanSendParticipationRequestToUserForProjectWhenAddedby()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $facilitator = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        (new ProjectService)->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'projectId' => $project->id,
                'participations' => [$facilitator->id => 'facilitator'],
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $project->id,
            'participant_id' => $facilitator->id,
            'participant_type' => $facilitator::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value,
        ]);

        $this->assertDatabaseHas('requests', [
            'from_id' => $user->id,
            'from_type' => $user::class,
            'for_id' => $project->id,
            'for_type' => $project::class,
            'to_id' => $facilitator->id,
            'to_type' => $facilitator::class,
            'type' => RequestTypeEnum::facilitator->value
        ]);

        $this->assertDatabaseHas('activities', [
            'performedby_id' => $user->id,
            'performedby_type' => $user::class,
            'performedon_id' => $project->id,
            'performedon_type' => $project::class,
            'action' => "sendParticipationRequests",
        ]);
    }

    public function testCanSendParticipationRequestToFacilitatorForProjectWhenAnAdmin()
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
        
        $facilitator = User::factory()
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

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        (new ProjectService)->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'projectId' => $project->id,
                'participations' => [$facilitator->id => 'facilitator'],
                'addedby' => $admin,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $project->id,
            'participant_id' => $facilitator->id,
            'participant_type' => $facilitator::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value,
        ]);

        $this->assertDatabaseMissing('requests', [
            'from_id' => $admin->id,
            'from_type' => $admin->id,
            'for_id' => $project->id,
            'for_type' => $project::class,
            'to_id' => $facilitator->id,
            'to_type' => $facilitator::class
        ]);

        $this->assertDatabaseHas('activities', [
            'performedby_id' => $admin->id,
            'performedby_type' => $admin::class,
            'performedon_id' => $project->id,
            'performedon_type' => $project::class,
            'action' => "sendParticipationRequests",
        ]);
    }

    public function testCanSendParticipationRequestToLearnerForProjectWhenAnAdmin()
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
        
        $student = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        (new ProjectService)->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'projectId' => $project->id,
                'participations' => [$student->id => 'learner'],
                'addedby' => $admin,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $project->id,
            'participant_id' => $student->id,
            'participant_type' => $student::class,
            'participating_as' => ProjectParticipantEnum::learner->value,
        ]);

        $this->assertDatabaseMissing('requests', [
            'from_id' => $admin->id,
            'from_type' => $admin->id,
            'for_id' => $project->id,
            'for_type' => $project::class,
            'to_id' => $student->id,
            'to_type' => $student::class
        ]);

        $this->assertDatabaseHas('activities', [
            'performedby_id' => $admin->id,
            'performedby_type' => $admin::class,
            'performedon_id' => $project->id,
            'performedon_type' => $project::class,
            'action' => "sendParticipationRequests",
        ]);
    }

    public function testCanSendParticipationRequestToLearnerForProjectWhenAProjectFacilitator()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $facilitator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $student = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($facilitator);
        $participation->save();

        $this->assertDatabaseHas('project_participant', [
            'project_id' => $project->id,
            'participant_id' => $facilitator->id,
            'participant_type' => $facilitator::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value,
        ]);
        
        (new ProjectService)->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'projectId' => $project->id,
                'participations' => [$student->id => 'learner'],
                'addedby' => $facilitator,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $project->id,
            'participant_id' => $student->id,
            'participant_type' => $student::class,
            'participating_as' => ProjectParticipantEnum::learner->value,
        ]);

        $this->assertDatabaseHas('requests', [
            'from_id' => $facilitator->id,
            'from_type' => $facilitator::class,
            'for_id' => $project->id,
            'for_type' => $project::class,
            'to_id' => $student->id,
            'to_type' => $student::class
        ]);

        $this->assertDatabaseHas('activities', [
            'performedby_id' => $facilitator->id,
            'performedby_type' => $facilitator::class,
            'performedon_id' => $project->id,
            'performedon_type' => $project::class,
            'action' => "sendParticipationRequests",
        ]);
    }

    public function testCanSendSponsorshipParticipationRequestToUserForProjectWhenAProjectFacilitator()
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
        
        $facilitator = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $student = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::STUDENT
            ]), [], 'userTypes')
            ->create();
        
        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'addedby' => $user,
            ])
        );

        $this->assertDatabaseHas('projects', [
            'name' => 'PHP',
            'description' => 'backend language for web sites',
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($facilitator);
        $participation->save();

        $this->assertDatabaseHas('project_participant', [
            'project_id' => $project->id,
            'participant_id' => $facilitator->id,
            'participant_type' => $facilitator::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value,
        ]);
        
        (new ProjectService)->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'projectId' => $project->id,
                'participations' => [$student->id => 'learner'],
                'addedby' => $facilitator,
            ])
        );

        $this->assertDatabaseMissing('project_participant', [
            'project_id' => $project->id,
            'participant_id' => $student->id,
            'participant_type' => $student::class,
            'participating_as' => ProjectParticipantEnum::learner->value,
        ]);

        $this->assertDatabaseMissing('requests', [
            'from_id' => $facilitator->id,
            'from_type' => $facilitator->id,
            'for_id' => $project->id,
            'for_type' => $project::class,
            'to_id' => $student->id,
            'to_type' => $student::class
        ]);

        $this->assertDatabaseHas('activities', [
            'performedby_id' => $facilitator->id,
            'performedby_type' => $facilitator::class,
            'performedon_id' => $project->id,
            'performedon_type' => $project::class,
            'action' => "sendParticipationRequests",
        ]);
    }

    public function testCannotRemoveAParticipantFromAProjectWithoutAddedby()
    {
        $this->expectException(ProjectException::class);

        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
        
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');

        $projectService->removeParticipants(
            ProjectDTO::new()->fromArray([
                'participations' => [
                    $participant->id => 'learner'
                ],
                'projectId' => $project->id
            ]));
    }

    public function testCannotRemoveAParticipantFromAProjectWithoutProjectId()
    {
        $this->expectException(ProjectException::class);

        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
        
        $this->expectExceptionMessage('Sorry! A valid project is required to perform this action.');

        $projectService->removeParticipants(
            ProjectDTO::new()->fromArray([
                'participations' => [
                    $participant->id => 'learner'
                ],
                'addedby' => $owner
            ]));
    }

    public function testCannotRemoveAParticipantFromAProjectWithAnEmptyParticipationArray()
    {
        $this->expectException(ProjectException::class);

        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
        
        $this->expectExceptionMessage("Sorry! The users and their respective participation type must be specified.");

        $projectService->removeParticipants(
            ProjectDTO::new()->fromArray([
                'participations' => [],
                'projectId' => $project->id,
                'addedby' => $owner
            ]));
    }

    public function testCannotRemoveAParticipantFromAProjectWithAParticipationArrayWhichIsNotAList()
    {
        $this->expectException(ProjectException::class);

        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
        
        $this->expectExceptionMessage("Sorry! You need to provide a list of user ids pointing to the participation type you wish to establish with the company/project.");

        $projectService->removeParticipants(
            ProjectDTO::new()->fromArray([
                'participations' => [
                    $participant->id
                ],
                'projectId' => $project->id,
                'addedby' => $owner
            ]));
    }

    public function testCannotRemoveAParticipantFromAProjectWithAParticipationArrayWhichDoesNotHaveStringOrArrayValues()
    {
        $this->expectException(ProjectException::class);

        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
        
        $this->expectExceptionMessage("Sorry! The user ids must point to respective participation types.");

        $projectService->removeParticipants(
            ProjectDTO::new()->fromArray([
                'participations' => [$participant->id => 1],
                'projectId' => $project->id,
                'addedby' => $owner
            ]));
    }

    public function testCannotRemoveAFakeUserFromAProject()
    {
        $this->expectException(ProjectException::class);

        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
        
        $this->expectExceptionMessage("Sorry, no participant was provided.");

        $projectService->removeParticipants(
            ProjectDTO::new()->fromArray([
                'participations' => [10 => 'facilitator'],
                'projectId' => $project->id,
                'addedby' => $owner
            ]));
    }

    public function testCannotRemoveAParticipantFromTheProjectBySpecifyingWrongParticipationType()
    {
        $this->expectException(ProjectException::class);

        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')
        ->hasAttached(UserType::factory([
            'name' => UserType::FACILITATOR
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
        
        $this->expectExceptionMessage("Sorry! User with name {$participant->name} is not participating as facilitator in this project.");

        $projectService->removeParticipants(
            ProjectDTO::new()->fromArray([
                'addedby' => $owner,
                'participations' => [
                    $participant->id => 'facilitator'
                ],
                'projectId' => $project->id
            ]));
    }

    public function testCannotRemoveANonParticipantFromTheProject()
    {
        $this->expectException(ProjectException::class);

        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $this->expectExceptionMessage("Sorry! User with name {$participant->name} is not participating as learner in this project.");

        $projectService->removeParticipants(
            ProjectDTO::new()->fromArray([
                'addedby' => $owner,
                'participations' => [
                    $participant->id => 'learner'
                ],
                'projectId' => $project->id
            ]));
    }

    public function testCannotRemoveAParticipantFromTheProjectBySpecifyingFakeParticipationType()
    {
        $this->expectException(ProjectException::class);

        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
        
        $this->expectExceptionMessage("Sorry, participant cannot participate in the project as FAKEPARTICIPATIONTYPE.");

        $projectService->removeParticipants(
            ProjectDTO::new()->fromArray([
                'addedby' => $owner,
                'participations' => [
                    $participant->id => 'fakeParticipationType'
                ],
                'projectId' => $project->id
            ]));
    }

    public function testCanRemoveAnOwnerAsLearnerFromAProjectWhenFacilitator()
    {
        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::FACILITATOR
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::facilitator->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($owner);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $owner::class,
            'participant_id' => $owner->id,
        ]);

        $projectService->removeParticipants(
            ProjectDTO::new()->fromArray([
                'addedby' => $participant,
                'participations' => [
                    $owner->id => 'learner'
                ],
                'projectId' => $project->id
            ]));

        $this->assertDatabaseMissing("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
    }

    public function testCanRemoveAParticipatingFacilitatorFromAProjectWhenOwner()
    {
        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::FACILITATOR
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::facilitator->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);

        $projectService->removeParticipants(
            ProjectDTO::new()->fromArray([
                'addedby' => $owner,
                'participations' => [
                    $participant->id => 'facilitator'
                ],
                'projectId' => $project->id
            ]));

        $this->assertDatabaseMissing("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
    }

    public function testCanRemoveAParticipatingLearnerFromAProjectWhenOwner()
    {
        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);

        $projectService->removeParticipants(
            ProjectDTO::new()->fromArray([
                'addedby' => $owner,
                'participations' => [
                    $participant->id => 'learner'
                ],
                'projectId' => $project->id
            ]));

        $this->assertDatabaseMissing("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
    }

    public function testCannotLeaveAProjectWithoutAddedby()
    {
        $this->expectException(ProjectException::class);

        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
        
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');

        $projectService->leaveProject(
            ProjectDTO::new()->fromArray([
                'projectId' => $project->id
            ]));
    }

    public function testCannotLeaveAProjectWithoutProjectId()
    {
        $this->expectException(ProjectException::class);

        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
        
        $this->expectExceptionMessage('Sorry! A valid project is required to perform this action.');

        $projectService->removeParticipants(
            ProjectDTO::new()->fromArray([
                'addedby' => $participant
            ]));
    }

    public function testCannotLeaveAProjectWithFakeParticipationType()
    {
        $this->expectException(ProjectException::class);

        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
        
        $this->expectExceptionMessage("Sorry, participant cannot participate in the project as FAKEPARTICIPATIONTYPE.");

        $projectService->leaveProject(
            ProjectDTO::new()->fromArray([
                'participationType' => "fakeParticipationType",
                'addedby' => $participant,
                'projectId' => $project->id
            ]));
    }

    public function testCannotLeaveAProjectWithWrongParticipationType()
    {
        $this->expectException(ProjectException::class);

        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->hasAttached(UserType::factory([
            'name' => UserType::FACILITATOR
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
        
        $this->expectExceptionMessage("Sorry! User with name {$participant->name} is not participating as facilitator in this project.");

        $projectService->leaveProject(
            ProjectDTO::new()->fromArray([
                'participationType' => "facilitator",
                'addedby' => $participant,
                'projectId' => $project->id
            ]));
    }

    public function testCanLeaveAProjectAsLearner()
    {
        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->hasAttached(UserType::factory([
            'name' => UserType::FACILITATOR
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
        
        $projectService->leaveProject(
            ProjectDTO::new()->fromArray([
                'participationType' => "learner",
                'addedby' => $participant,
                'projectId' => $project->id
            ]));

        $this->assertDatabaseMissing("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
    }

    public function testCanLeaveAProjectAsFacilitator()
    {
        $owner = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->create();

        $participant = User::factory()->hasAttached(UserType::factory([
            'name' => UserType::STUDENT
        ]),[], 'userTypes')->hasAttached(UserType::factory([
            'name' => UserType::FACILITATOR
        ]),[], 'userTypes')->create();

        $projectService = (new ProjectService);

        $project = $projectService->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $owner
            ])
        );

        $this->assertDatabaseHas("projects", [
            'name' => $project->name,
            'description' => $project->description,
            'addedby_type' => $owner::class,
            'addedby_id' => $owner->id,
        ]);
        
        $participation = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::facilitator->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
        
        $projectService->leaveProject(
            ProjectDTO::new()->fromArray([
                'participationType' => "facilitator",
                'addedby' => $participant,
                'projectId' => $project->id
            ]));

        $this->assertDatabaseMissing("project_participant", [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::learner->value,
            'participant_type' => $participant::class,
            'participant_id' => $participant->id,
        ]);
    }
}
