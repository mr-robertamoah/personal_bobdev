<?php

namespace Tests\Feature;

use App\DTOs\ProjectDTO;
use App\DTOs\SkillDTO;
use App\DTOs\SkillTypeDTO;
use App\Enums\PaginationEnum;
use App\Enums\ProjectParticipantEnum;
use App\Enums\RelationshipTypeEnum;
use App\Enums\RequestStateEnum;
use App\Enums\RequestTypeEnum;
use App\Models\Company;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserType;
use App\Services\ProjectService;
use App\Services\SkillService;
use App\Services\SkillTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp() : void
    {
        parent::setUp();

        if (Schema::hasTable('projects'))
        {
            DB::table('projects')->truncate();
        }
        if (Schema::hasTable('user_types'))
        {
            DB::table('user_types')->truncate();
        }
        if (Schema::hasTable('users'))
        {
            DB::table('users')->truncate();
        }
    }
    
    public function testCannotCreateProjectWithoutName()
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

        $this->actingAs($user);

        $data = [
            'description' => $this->faker->sentence(),
        ];

        $response = $this->postJson('/api/project', $data);

        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                'name' => [
                    'The name field is required.'
                ],
            ]
        ]);
    }
    
    public function testCannotCreateProjectWithoutDescription()
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

        $this->actingAs($user);

        $data = [
            'name' => $this->faker->name(),
        ];

        $response = $this->postJson('/api/project', $data);

        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                'description' => [
                    'The description field is required.'
                ],
            ]
        ]);
    }
    
    public function testCannotCreateProjectWithoutHavingValidUserType()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $this->actingAs($user);

        $data = [
            'name' => $this->faker->sentence(),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->postJson('/api/project', $data);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => "This action is unauthorized.",
            ]
        );
    }
    
    public function testCanCreateProjectAsAdmin()
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

        $this->actingAs($user);

        $data = [
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->postJson('/api/project', $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'project' => [
                'name' => $data['name'],
                'description' => $data['description'],
            ]
        ]);
    }
    
    public function testCannotCreateProjectAsAdminForAnotherUserWithoutValidUserType()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $admin = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $this->actingAs($admin);

        $data = [
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence(),
            'for' => 'user',
            'forId' => $user->id
        ];

        $response = $this->postJson('/api/project', $data);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Sorry! You are not authorized to create a project.'
        ]);
    }
    
    public function testCanCreateProjectAsAdminForAnotherUserWithValidUserType()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $admin = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $admin->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType->id);

        $this->actingAs($admin);

        $data = [
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence(),
            'for' => 'user',
            'forId' => $user->id
        ];

        $response = $this->postJson('/api/project', $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'project' => [
                'name' => $data['name'],
                'description' => $data['description'],
                'owner' => [
                    'username' => $user->username,
                    'id' => $user->id,
                ]
            ]
        ]);
    }
    
    public function testCanCreateProjectAsFacilitator()
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
            'name' => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $data = [
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->postJson('/api/project', $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'project' => [
                'name' => $data['name'],
                'description' => $data['description'],
            ]
        ]);
    }
    
    public function testCanCreateProjectAsLearner()
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
            'name' => UserType::STUDENT
        ]);

        $user->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $data = [
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->postJson('/api/project', $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'project' => [
                'name' => $data['name'],
                'description' => $data['description'],
            ]
        ]);
    }
    
    public function testCannotUpdateProjectWithoutNameAndDescription()
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

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $data = [];

        $response = $this->postJson("/api/project/{$project->id}", $data);

        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                'name' => [
                    'The name field is required when description is not present.'
                ],
                'description' => [
                    'The description field is required when name is not present.'
                ],
            ]
        ]);
    }
    
    public function testCannotUpdateProjectWithWrongProjectId()
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

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $data = ['name' => $this->faker->sentence()];

        $response = $this->postJson("/api/project/10", $data);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Sorry! A valid project is required to perform this action.'
        ]);
    }
    
    public function testCannotUpdateProjectWhenNotOwnerOrAdmin()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other->userTypes()->attach($userType->id);

        $this->actingAs($other);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $data = ['name' => $this->faker->sentence()];

        $response = $this->postJson("/api/project/{$project->id}", $data);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => "Sorry! You are not authorized to update a project."
        ]);
    }
    
    public function testCanUpdateProjectWhenOwner()
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

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $data = ['name' => $this->faker->name()];

        $response = $this->postJson("/api/project/{$project->id}", $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'project' => [
                'name' => $data['name']
            ]
        ]);
    }
    
    public function testCanUpdateProjectWhenNotOwnerButAnAdmin()
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

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $data = ['name' => $this->faker->name()];

        $response = $this->postJson("/api/project/{$project->id}", $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'project' => [
                'name' => $data['name']
            ]
        ]);
    }
    
    public function testCannotDeleteProjectWithWrongProjectId()
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

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $response = $this->deleteJson("/api/project/10");

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Sorry! A valid project is required to perform this action.'
        ]);
    }
    
    public function testCannotDeleteProjectWhenNotOwnerOrAdmin()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other->userTypes()->attach($userType->id);

        $this->actingAs($other);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $response = $this->deleteJson("/api/project/{$project->id}");

        $response->assertStatus(500);
        $response->assertJson([
            'message' => "Sorry! You are not authorized to delete a project."
        ]);
    }
    
    public function testCanDeleteProjectWhenOwner()
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

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $response = $this->deleteJson("/api/project/{$project->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
    
    public function testCanDeleteProjectWhenNotOwnerButAnAdmin()
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

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $response = $this->deleteJson("/api/project/{$project->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
    
    public function testCannotAddSkillsToProjectWithoutSkillIds()
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

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $response = $this->postJson("/api/project/{$project->id}/add_skills", []);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The skill ids field is required.'
        ]);
    }
    
    public function testCannotAddSkillsToFakeProject()
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

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $skillService = (new SkillService);
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );

        $skills = [];
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Python',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;

        $data = [
            'skillIds' => $skills
        ];
        $response = $this->postJson("/api/project/10/add_skills", $data);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Sorry! A valid project is required to perform this action.'
        ]);
    }
    
    public function testCannotAddSkillsToProjectIfNotFacilitorAdminOrOwner()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $other->userTypes()->attach($userType->id);

        $this->actingAs($other);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $skillService = (new SkillService);
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );

        $skills = [];
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Python',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;

        $data = [
            'skillIds' => $skills
        ];
        $response = $this->postJson("/api/project/{$project->id}/add_skills", $data);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => "Sorry! You are not authorized to update a project."
        ]);
    }
    
    public function testCanAddSkillsToProjectWhenAdmin()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $other->userTypes()->attach($userType->id);

        $this->actingAs($other);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $skillService = (new SkillService);
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );

        $skills = [];
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Python',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;

        $data = [
            'skillIds' => $skills
        ];
        $response = $this->postJson("/api/project/{$project->id}/add_skills", $data);

        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[0]
        ]);
        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[1]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'project' => [
                'name' => $project->name
            ]
        ]);
    }
    
    public function testCanAddSkillsToProjectWhenOwner()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $other->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $skillService = (new SkillService);
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );

        $skills = [];
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Python',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;

        $data = [
            'skillIds' => $skills
        ];
        $response = $this->postJson("/api/project/{$project->id}/add_skills", $data);

        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[0]
        ]);
        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[1]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'project' => [
                'name' => $project->name
            ]
        ]);
    }
    
    public function testCanAddSkillsToProjectWhenParticipatingAsFacilitator()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other->userTypes()->attach($userType->id);

        $this->actingAs($other);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $projectParticipant = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        $projectParticipant->participant()->associate($other);
        $projectParticipant->save();

        $this->assertDatabaseHas('project_participant', [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::facilitator->value,
            'participant_type' => $other::class,
            'participant_id' => $other->id,
        ]);

        $skillService = (new SkillService);
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );

        $skills = [];
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Python',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;

        $data = [
            'skillIds' => $skills
        ];
        $response = $this->postJson("/api/project/{$project->id}/add_skills", $data);

        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[0]
        ]);
        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[1]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'project' => [
                'name' => $project->name
            ]
        ]);
    }
    
    public function testCannotRemoveSkillsFromAProjectWithoutSkillIds()
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

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $response = $this->postJson("/api/project/{$project->id}/remove_skills", []);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The skill ids field is required.'
        ]);
    }
    
    public function testCannotRemoveSkillsFromAFakeProject()
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

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $skillService = (new SkillService);
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );

        $skills = [];
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Python',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;

        $data = [
            'skillIds' => $skills
        ];
        $response = $this->postJson("/api/project/10/remove_skills", $data);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Sorry! A valid project is required to perform this action.'
        ]);
    }
    
    public function testCannotRemoveSkillsFromAProjectIfNotFacilitorAdminOrOwner()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $other->userTypes()->attach($userType->id);

        $this->actingAs($other);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $skillService = (new SkillService);
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );

        $skills = [];
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Python',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;

        $data = [
            'skillIds' => $skills
        ];
        $response = $this->postJson("/api/project/{$project->id}/remove_skills", $data);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => "Sorry! You are not authorized to update a project."
        ]);
    }
    
    public function testCanRemoveSkillsFromProjectWhenAdmin()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $other->userTypes()->attach($userType->id);

        $this->actingAs($other);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $skillService = (new SkillService);
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );

        $skills = [];
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Python',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;

        $project->skills()->attach($skills);

        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[0]
        ]);
        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[1]
        ]);

        $data = [
            'skillIds' => $skills
        ];
        $response = $this->postJson("/api/project/{$project->id}/remove_skills", $data);

        $this->assertDatabaseMissing('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[0]
        ]);
        $this->assertDatabaseMissing('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[1]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'project' => [
                'name' => $project->name
            ]
        ]);
    }
    
    public function testCanRemoveSkillsFromProjectWhenOwner()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $other->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $skillService = (new SkillService);
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );

        $skills = [];
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Python',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;

        $project->skills()->attach($skills);

        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[0]
        ]);
        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[1]
        ]);

        $data = [
            'skillIds' => $skills
        ];
        $response = $this->postJson("/api/project/{$project->id}/remove_skills", $data);

        $this->assertDatabaseMissing('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[0]
        ]);
        $this->assertDatabaseMissing('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[1]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'project' => [
                'name' => $project->name
            ]
        ]);
    }
    
    public function testCanRemoveSkillsFromAProjectWhenParticipatingAsFacilitator()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other->userTypes()->attach($userType->id);

        $this->actingAs($other);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $projectParticipant = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        $projectParticipant->participant()->associate($other);
        $projectParticipant->save();

        $this->assertDatabaseHas('project_participant', [
            'project_id' => $project->id,
            'participating_as' => ProjectParticipantEnum::facilitator->value,
            'participant_type' => $other::class,
            'participant_id' => $other->id,
        ]);

        $skillService = (new SkillService);
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'description' => 'coding for making programs and software',
                'addedBy' => $user,
            ])
        );

        $skills = [];
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;
        $skills[] = $skillService->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'Python',
                'description' => 'backend language for web sites',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        )?->id;

        $project->skills()->attach($skills);
        
        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[0]
        ]);
        $this->assertDatabaseHas('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[1]
        ]);

        $data = [
            'skillIds' => $skills
        ];
        $response = $this->postJson("/api/project/{$project->id}/remove_skills", $data);

        $this->assertDatabaseMissing('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[0]
        ]);
        $this->assertDatabaseMissing('project_skill', [
            'project_id' => $project->id,
            'skill_id' => $skills[1]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'project' => [
                'name' => $project->name
            ]
        ]);
    }
    
    public function testCannotSendParticipationInvitationsForProjectWithoutParticipations()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $data = [];
        $response = $this->postJson("/api/project/{$project->id}/invite_participants", $data);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => "The participations field is required."
        ]);
    }
    
    public function testCannotSendParticipationInvitationsForProjectWithParticipationsThatIsNotAnArray()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $data = ["participations" => "string"];
        $response = $this->postJson("/api/project/{$project->id}/invite_participants", $data);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => "The participations must be an array."
        ]);
    }
    
    public function testCannotSendParticipationInvitationsToAlreadyParticipatingUserForProject()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $projectPartication = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        $projectPartication->participant()->associate($other);
        $projectPartication->save();

        $this->assertDatabaseHas('project_participant', [
            'participant_id' => $other->id,
            'participant_type' => $other::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);

        $data = [
            "participations" => [
                $other->id => ProjectParticipantEnum::facilitator->value
            ]
        ];
        $response = $this->postJson("/api/project/{$project->id}/invite_participants", $data);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => "Sorry! User is already participating in this project."
        ]);
    }
    
    public function testCannotSendParticipationInvitationsForProjectWithInvalidProject()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $data = [
            "participations" => [
                $other->id => ProjectParticipantEnum::facilitator->value
            ]
        ];

        $response = $this->postJson("/api/project/10/invite_participants", $data);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Sorry! A valid project is required to perform this action.'
        ]);
    }
    
    public function testCannotSendParticipationInvitationsForProjectWithInvalidParticipationsData()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $data = [
            "participations" => [
                $other->id
            ]
        ];

        $response = $this->postJson("/api/project/{$project->id}/invite_participants", $data);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => "Sorry! You need to provide a list of user ids pointing to the participation type you wish to establish with the company/project."
        ]);
    }
    
    public function testCannotSendParticipationInvitationsForProjectWhenNotAdminOwnerOrFacilitator()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $potentialFacilitator = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah2@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other->userTypes()->attach($userType->id);
        $potentialFacilitator->userTypes()->attach($userType->id);

        $this->actingAs($other);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $data = [
            "participations" => [
                $potentialFacilitator->id => ProjectParticipantEnum::facilitator->value,
            ]
        ];
        $response = $this->postJson("/api/project/{$project->id}/invite_participants", $data);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Sorry! You are not authorized to update a project.'
        ]);
    }
    
    public function testCanSendParticipationInvitationsToMultipleUsersForProject()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other1 = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other2 = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah2@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other1->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $other2->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $data = [
            "participations" => [
                $other2->id => ProjectParticipantEnum::learner->value,
                $other1->id => ProjectParticipantEnum::facilitator->value
            ]
        ];
        $response = $this->postJson("/api/project/{$project->id}/invite_participants", $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $this->assertDatabaseHas('requests', [
            'from_id' => $user->id,
            'from_type' => $user::class,
            'for_id' => $project->id,
            'for_type' => $project::class,
            'to_id' => $other2->id,
            'to_type' => $other2::class,
            'state' => RequestStateEnum::pending->value,
            'type' => RequestTypeEnum::learner->value
        ]);

        $this->assertDatabaseHas('requests', [
            'from_id' => $user->id,
            'from_type' => $user::class,
            'for_id' => $project->id,
            'for_type' => $project::class,
            'to_id' => $other1->id,
            'to_type' => $other1::class,
            'state' => RequestStateEnum::pending->value,
            'type' => RequestTypeEnum::facilitator->value
        ]);
    }
    
    public function testCanRemoveMultipleParticipantsFromProjectWhenAdmin()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other1 = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $admin = User::create([
            'username' => "mr_robertamoahadmin",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoahadmin@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other2 = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah2@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $admin->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other1->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $other2->userTypes()->attach($userType->id);

        $this->actingAs($admin);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $projectPartication = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        $projectPartication->participant()->associate($other1);
        $projectPartication->save();

        $this->assertDatabaseHas('project_participant', [
            'participant_id' => $other1->id,
            'participant_type' => $other1::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);

        $projectPartication = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $projectPartication->participant()->associate($other2);
        $projectPartication->save();

        $this->assertDatabaseHas('project_participant', [
            'participant_id' => $other2->id,
            'participant_type' => $other2::class,
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);

        $data = [
            "participations" => [
                $other2->id => ProjectParticipantEnum::learner->value,
                $other1->id => ProjectParticipantEnum::facilitator->value
            ]
        ];
        $response = $this->postJson("/api/project/{$project->id}/remove_participants", $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $this->assertDatabaseMissing('project_participant', [
            'participant_id' => $other1->id,
            'participant_type' => $other1::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);

        $this->assertDatabaseMissing('project_participant', [
            'participant_id' => $other2->id,
            'participant_type' => $other2::class,
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
    }
    
    public function testCanRemoveMultipleLearnersFromProjectWhenFacilitator()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other1 = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $facilitator = User::create([
            'username' => "mr_robertamoahadmin",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoahadmin@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other2 = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah2@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $facilitator->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $other1->userTypes()->attach($userType->id);

        $other2->userTypes()->attach($userType->id);

        $this->actingAs($facilitator);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $projectPartication = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        $projectPartication->participant()->associate($facilitator);
        $projectPartication->save();

        $this->assertDatabaseHas('project_participant', [
            'participant_id' => $facilitator->id,
            'participant_type' => $facilitator::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);

        $projectPartication = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $projectPartication->participant()->associate($other1);
        $projectPartication->save();

        $this->assertDatabaseHas('project_participant', [
            'participant_id' => $other1->id,
            'participant_type' => $other1::class,
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);

        $projectPartication = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $projectPartication->participant()->associate($other2);
        $projectPartication->save();

        $this->assertDatabaseHas('project_participant', [
            'participant_id' => $other2->id,
            'participant_type' => $other2::class,
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);

        $data = [
            "participations" => [
                $other2->id => ProjectParticipantEnum::learner->value,
                $other1->id => ProjectParticipantEnum::learner->value
            ]
        ];
        $response = $this->postJson("/api/project/{$project->id}/remove_participants", $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $this->assertDatabaseMissing('project_participant', [
            'participant_id' => $other1->id,
            'participant_type' => $other1::class,
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);

        $this->assertDatabaseMissing('project_participant', [
            'participant_id' => $other2->id,
            'participant_type' => $other2::class,
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
    }
    
    public function testCanRemoveMultipleParticipantsFromProjectWhenOwner()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other1 = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $other2 = User::create([
            'username' => "mr_robertamoah2",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah2@yahoo.com",
            'dob' => now()->subYears(20)->toDateTimeString()
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $other1->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::STUDENT
        ]);

        $other2->userTypes()->attach($userType->id);

        $this->actingAs($user);

        $project = (new ProjectService)->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'description' => $this->faker->sentence(),
                'addedby' => $user
            ])
        );

        $projectPartication = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);
        $projectPartication->participant()->associate($other1);
        $projectPartication->save();

        $this->assertDatabaseHas('project_participant', [
            'participant_id' => $other1->id,
            'participant_type' => $other1::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);

        $projectPartication = $project->participants()->create([
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
        $projectPartication->participant()->associate($other2);
        $projectPartication->save();

        $this->assertDatabaseHas('project_participant', [
            'participant_id' => $other2->id,
            'participant_type' => $other2::class,
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);

        $data = [
            "participations" => [
                $other2->id => ProjectParticipantEnum::learner->value,
                $other1->id => ProjectParticipantEnum::facilitator->value
            ]
        ];
        $response = $this->postJson("/api/project/{$project->id}/remove_participants", $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $this->assertDatabaseMissing('project_participant', [
            'participant_id' => $other1->id,
            'participant_type' => $other1::class,
            'participating_as' => ProjectParticipantEnum::facilitator->value
        ]);

        $this->assertDatabaseMissing('project_participant', [
            'participant_id' => $other2->id,
            'participant_type' => $other2::class,
            'participating_as' => ProjectParticipantEnum::learner->value
        ]);
    }

    public function testCannotGetProjectsWithoutAppropriatePairOfQueryData()
    {
        $query = "participant_id=1&owner_id=1";
        $response = $this->getJson("/api/projects?". $query);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "The participant type field is required when participant id is present. (and 1 more error)",
            "errors" => [
                "participant_type" => [
                    'The participant type field is required when participant id is present.'
                ],
                "owner_type" => [
                    'The owner type field is required when owner id is present.'
                ],
            ]
        ]);
    }

    public function testCanGetAllProjectsWhenGuest()
    {
        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $projects = Project::factory()->count(20)
            ->create([
                "addedby_type" => $creator::class,
                "addedby_id" => $creator->id,
            ]);

        $query = "";
        $response = $this->getJson("/api/projects?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        $this->assertEquals(
            $data->meta->total,
            $projects->count()
        );
        $this->assertEquals(
            count($data->data),
            PaginationEnum::getUsers->value
        );

        if (is_null($data->links->next)) return;

        $response = $this->getJson($data->links->next);
        
        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        $this->assertEquals(
            count($data->data),
            PaginationEnum::getUsers->value
        );
    }

    public function testCanGetAllProjectsWhenAdmin()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            "name" => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType);

        $projects = Project::factory()->count(20)
            ->create([
                "addedby_type" => $creator::class,
                "addedby_id" => $creator->id,
            ]);

        $this->actingAs($user);

        $query = "";
        $response = $this->getJson("/api/projects?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        $this->assertEquals(
            $data->meta->total,
            $projects->count()
        );
        $this->assertEquals(
            count($data->data),
            PaginationEnum::getUsers->value
        );

        if (is_null($data->links->next)) return;

        $response = $this->getJson($data->links->next);
        
        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        $this->assertEquals(
            count($data->data),
            PaginationEnum::getUsers->value
        );
    }

    public function testCanGetAllProjectsWhenUser()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);

        $projects = Project::factory()->count(20)
            ->create([
                "addedby_type" => $creator::class,
                "addedby_id" => $creator->id,
            ]);

        $this->actingAs($user);

        $query = "";
        $response = $this->getJson("/api/projects?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        $this->assertEquals(
            $data->meta->total,
            $projects->count()
        );
        $this->assertEquals(
            count($data->data),
            PaginationEnum::getUsers->value
        );

        if (is_null($data->links->next)) return;

        $response = $this->getJson($data->links->next);
        
        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        $this->assertEquals(
            count($data->data),
            PaginationEnum::getUsers->value
        );
    }

    public function testCanGetAllProjectsWithNameQuery()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);

        $projects = Project::factory()->count(20)
            ->create([
                "addedby_type" => $creator::class,
                "addedby_id" => $creator->id,
            ]);

        $this->actingAs($user);

        $name = "ac";
        $query = "name={$name}";
        $response = $this->getJson("/api/projects?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        
        $this->assertEquals(
            $data->meta->total,
            count(array_filter($projects->toArray(), function ($value) use ($name) {
                return str_contains($value["name"], $name);
            }))
        );
    }

    public function testCanGetAllProjectsWithOwnerQuery()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator1 = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator2 = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);

        $creator2ProjectCount = 10;
        $projects = array_merge(Project::factory()->count(10)
            ->create([
                "addedby_type" => $creator1::class,
                "addedby_id" => $creator1->id,
            ])->toArray(), Project::factory()->count($creator2ProjectCount)
            ->create([
                "addedby_type" => $creator2::class,
                "addedby_id" => $creator2->id,
            ])->toArray());

        $this->actingAs($user);

        $query = "owner_id={$creator1->id}&owner_type=user";
        $response = $this->getJson("/api/projects?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        
        $this->assertEquals(
            $data->meta->total,
            $creator2ProjectCount
        );
    }

    public function testCanGetAllProjectsWithOfficialOfOwningCompany()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $official = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);
        $creator->userTypes()->attach($userType);

        $company = Company::factory()->create(["user_id" => $creator->id]);
        $relation = $company->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($official);
        $relation->save();
        
        $companyOwnedProjectCount = 5;
        $projects = array_merge(Project::factory()->count(10)
            ->create([
                "addedby_type" => $creator::class,
                "addedby_id" => $creator->id,
            ])->toArray(), Project::factory()->count($companyOwnedProjectCount)
            ->create([
                "addedby_type" => $company::class,
                "addedby_id" => $company->id,
            ])->toArray());

        $this->actingAs($user);

        $query = "official_id={$official->id}";
        $response = $this->getJson("/api/projects?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        ds($data);
        $this->assertEquals(
            $data->meta->total,
            $companyOwnedProjectCount
        );
    }

    public function testCanGetAllProjectsWithMemberOfOwningCompany()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $member = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);
        $creator->userTypes()->attach($userType);

        $company = Company::factory()->create(["user_id" => $creator->id]);
        $relation = $company->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($member);
        $relation->save();
        
        $companyOwnedProjectCount = 5;
        $projects = array_merge(Project::factory()->count(10)
            ->create([
                "addedby_type" => $creator::class,
                "addedby_id" => $creator->id,
            ])->toArray(), Project::factory()->count($companyOwnedProjectCount)
            ->create([
                "addedby_type" => $company::class,
                "addedby_id" => $company->id,
            ])->toArray());

        $this->actingAs($user);

        $query = "member_id={$member->id}";
        $response = $this->getJson("/api/projects?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        
        $this->assertEquals(
            $data->meta->total,
            $companyOwnedProjectCount
        );
    }

    public function testCanGetAllProjectsWithParticipantOfProjectQuery()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $participant = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);
        $creator->userTypes()->attach($userType);

        $company = Company::factory()->create(["user_id" => $creator->id]);
        
        $companyOwnedProjectCount = 5;
        $projects = Project::factory()->count(10)
            ->create([
                "addedby_type" => $creator::class,
                "addedby_id" => $creator->id,
            ])->merge(Project::factory()->count($companyOwnedProjectCount)
            ->create([
                "addedby_type" => $company::class,
                "addedby_id" => $company->id,
            ]));

        $participation = $projects[0]->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $participation = $projects[1]->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            "project_id" => $projects[0]->id,
            "participant_type" => $participant::class,
            "participant_id" => $participant->id,
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);

        $this->assertDatabaseHas("project_participant", [
            "project_id" => $projects[1]->id,
            "participant_type" => $participant::class,
            "participant_id" => $participant->id,
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);

        $this->actingAs($user);

        $query = "participant_id={$participant->id}&participant_type=user";
        $response = $this->getJson("/api/projects?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        
        $this->assertEquals(
            $data->meta->total,
            2
        );
    }

    public function testCanGetAllProjectsWithParticipationTypeQuery()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $participant = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);
        $creator->userTypes()->attach($userType);

        $company = Company::factory()->create(["user_id" => $creator->id]);
        
        $companyOwnedProjectCount = 5;
        $projects = Project::factory()->count(10)
            ->create([
                "addedby_type" => $creator::class,
                "addedby_id" => $creator->id,
            ])->merge(Project::factory()->count($companyOwnedProjectCount)
            ->create([
                "addedby_type" => $company::class,
                "addedby_id" => $company->id,
            ]));

        $participation = $projects[0]->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $participation = $projects[1]->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            "project_id" => $projects[0]->id,
            "participant_type" => $participant::class,
            "participant_id" => $participant->id,
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);

        $this->assertDatabaseHas("project_participant", [
            "project_id" => $projects[1]->id,
            "participant_type" => $participant::class,
            "participant_id" => $participant->id,
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);

        $this->actingAs($user);

        $query = "participation_type=facilitator";
        $response = $this->getJson("/api/projects?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        
        $this->assertEquals(
            $data->meta->total,
            2
        );
    }

    public function testCanGetAllProjectsWithSkillsHavingNameQuery()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);
        $creator->userTypes()->attach($userType);

        $skill0 = Skill::factory()->create([
            "user_id" => $creator->id,
            "name" => "Web development"
        ]);
        
        $skill1 = Skill::factory()->create([
            "user_id" => $creator->id,
            "name" => "Mobile app development"
        ]);
        
        $projects = Project::factory()->count(20)
            ->create([
                "addedby_type" => $creator::class,
                "addedby_id" => $creator->id,
            ]);

        $projects[0]->skills()->attach($skill0);
        $projects[1]->skills()->attach($skill1);

        $this->assertDatabaseHas("project_skill", [
            "project_id" => $projects[0]->id,
            "skill_id" => $skill0->id
        ]);

        $this->assertDatabaseHas("project_skill", [
            "project_id" => $projects[1]->id,
            "skill_id" => $skill1->id,
        ]);

        $this->actingAs($user);

        $query = "skill_name=development";
        $response = $this->getJson("/api/projects?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        
        $this->assertEquals(
            $data->meta->total,
            2
        );
    }

    public function testCanGetAllProjectsWithParticipantAndParticipationTypeOfProjectQuery()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $creator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $participant = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $learner = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $userType = $user->addedUserTypes()->create([
            "name" => UserType::FACILITATOR
        ]);

        $user->userTypes()->attach($userType);
        $creator->userTypes()->attach($userType);

        $company = Company::factory()->create(["user_id" => $creator->id]);
        
        $companyOwnedProjectCount = 5;
        $projects = Project::factory()->count(10)
            ->create([
                "addedby_type" => $creator::class,
                "addedby_id" => $creator->id,
            ])->merge(Project::factory()->count($companyOwnedProjectCount)
            ->create([
                "addedby_type" => $company::class,
                "addedby_id" => $company->id,
            ]));

        $participation = $projects[0]->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $participation = $projects[1]->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($participant);
        $participation->save();

        $participation = $projects[2]->participants()->create([
            "participating_as" => ProjectParticipantEnum::learner->value
        ]);
        $participation->participant()->associate($learner);
        $participation->save();

        $this->assertDatabaseHas("project_participant", [
            "project_id" => $projects[0]->id,
            "participant_type" => $participant::class,
            "participant_id" => $participant->id,
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);

        $this->assertDatabaseHas("project_participant", [
            "project_id" => $projects[1]->id,
            "participant_type" => $participant::class,
            "participant_id" => $participant->id,
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);

        $this->assertDatabaseHas("project_participant", [
            "project_id" => $projects[2]->id,
            "participant_type" => $learner::class,
            "participant_id" => $learner->id,
            "participating_as" => ProjectParticipantEnum::learner->value
        ]);

        $this->actingAs($user);

        $query = "participation_type=facilitator&participant_id={$participant->id}&participant_type=user";
        $response = $this->getJson("/api/projects?". $query);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [],
            "links" => [],
            "meta" => [],
        ]);

        $data = json_decode($response->baseResponse->content());
        $this->assertEquals(
            $data->meta->total,
            2
        );
    }

    public function testCanGetDetailsOfProject()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $facilitator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $sponsor = Company::create([
            'alias' => $this->faker->userName(),
            'name' => $this->faker->company(),
            'user_id' => $user->id,
        ]);

        $project = Project::factory()->create([
            "addedby_id" => $user->id,
            "addedby_type" => $user::class,
        ]);

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::sponsor->value
        ]);
        $participation->participant()->associate($sponsor);
        $participation->save();

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($facilitator);
        $participation->save();

        $response = $this->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
            "project" => [
                "id" => $project->id,
                "participants" => [
                    [
                        "participatingAs" => "sponsor",
                        "participant" => [
                            "name" => $sponsor->name
                        ]
                    ],
                    [
                        "participatingAs" => "facilitator",
                        "participant" => [
                            "name" => $facilitator->name
                        ]
                    ],
                ]
            ]
        ]);
    }

    public function testCannotGetParticipantsOfProjectWithInvalidType()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $facilitator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $sponsor = Company::create([
            'alias' => $this->faker->userName(),
            'name' => $this->faker->company(),
            'user_id' => $user->id,
        ]);

        $project = Project::factory()->create([
            "addedby_id" => $user->id,
            "addedby_type" => $user::class,
        ]);

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::sponsor->value
        ]);
        $participation->participant()->associate($sponsor);
        $participation->save();

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($facilitator);
        $participation->save();

        $response = $this->getJson("/api/projects/{$project->id}/wrong_type");

        $response->assertStatus(404);
        $response->assertJson([
            "status" => false,
            "message" => "this is an unknown request."
        ]);
    }

    public function testCanGetParticipantsOfProject()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $facilitator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $sponsor = Company::create([
            'alias' => $this->faker->userName(),
            'name' => $this->faker->company(),
            'user_id' => $user->id,
        ]);

        $project = Project::factory()->create([
            "addedby_id" => $user->id,
            "addedby_type" => $user::class,
        ]);

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::sponsor->value
        ]);
        $participation->participant()->associate($sponsor);
        $participation->save();

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($facilitator);
        $participation->save();

        $response = $this->getJson("/api/projects/{$project->id}/participants");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
            "participants" => [
                [
                    "participatingAs" => "sponsor",
                    "participant" => [
                        "name" => $sponsor->name
                    ]
                ],
                [
                    "participatingAs" => "facilitator",
                    "participant" => [
                        "name" => $facilitator->name
                    ]
                ],
            ]
        ]);
    }

    public function testCanGetOfficialsOfProject()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $facilitator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $sponsor = Company::create([
            'alias' => $this->faker->userName(),
            'name' => $this->faker->company(),
            'user_id' => $user->id,
        ]);

        $project = Project::factory()->create([
            "addedby_id" => $user->id,
            "addedby_type" => $user::class,
        ]);

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::sponsor->value
        ]);
        $participation->participant()->associate($sponsor);
        $participation->save();

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($facilitator);
        $participation->save();

        $response = $this->getJson("/api/projects/{$project->id}/officials");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
            "participants" => [
                [
                    "participatingAs" => "facilitator",
                    "participant" => [
                        "name" => $facilitator->name
                    ]
                ],
            ]
        ]);

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())->participants),
            1
        );
    }

    public function testCanGetSponsorsOfProject()
    {
        $user = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $facilitator = User::create([
            'username' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => bcrypt("password"),
            'email' => $this->faker->email(),
        ]);

        $sponsor = Company::create([
            'alias' => $this->faker->userName(),
            'name' => $this->faker->company(),
            'user_id' => $user->id,
        ]);

        $project = Project::factory()->create([
            "addedby_id" => $user->id,
            "addedby_type" => $user::class,
        ]);

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::sponsor->value
        ]);
        $participation->participant()->associate($sponsor);
        $participation->save();

        $participation = $project->participants()->create([
            "participating_as" => ProjectParticipantEnum::facilitator->value
        ]);
        $participation->participant()->associate($facilitator);
        $participation->save();

        $response = $this->getJson("/api/projects/{$project->id}/sponsors");

        $response->assertStatus(200);
        $response->assertJson([
            "status" => true,
            "participants" => [
                [
                    "participatingAs" => "sponsor",
                    "participant" => [
                        "name" => $sponsor->name
                    ]
                ],
            ]
        ]);

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())->participants),
            1
        );
    }

    // todo get projectsessions
}
