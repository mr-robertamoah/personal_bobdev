<?php

namespace Tests\Unit;

use App\DTOs\ProjectDTO;
use App\DTOs\SkillDTO;
use App\DTOs\SkillTypeDTO;
use App\Exceptions\ProjectException;
use App\Exceptions\SkillException;
use App\Models\User;
use App\Models\UserType;
use App\Services\ProjectService;
use App\Services\SkillService;
use App\Services\SkillTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

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

    public function testCannotDeleteProjectWithoutAddedby()
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

    public function testCannotAttachInvalidSkillToProjectWhenAddedby()
    {
        $this->expectException(SkillException::class);
        $this->expectExceptionMessage("Sorry, no skill with id 1 exists.");

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
}
