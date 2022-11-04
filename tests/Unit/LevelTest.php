<?php

namespace Tests\Unit;

use App\DTOs\JobDTO;
use App\DTOs\LevelCollectionDTO;
use App\DTOs\LevelDTO;
use App\DTOs\SkillDTO;
use App\Exceptions\LevelException;
use App\Models\Level;
use App\Models\LevelCollection;
use App\Models\SkillType;
use App\Models\User;
use App\Models\UserType;
use App\Services\JobService;
use App\Services\LevelCollectionService;
use App\Services\LevelService;
use App\Services\SkillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LevelTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateLevelWithoutUser()
    {
        $this->expectException(LevelException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');

        (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
            ])
        );
    }

    public function testCannotCreateLevelWithoutBeingAnAdminOrFacilitator()
    {
        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to create a level.");

        $user = User::factory()->create();

        (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user
            ])
        );
    }

    public function testCannotCreateLevelWithoutName()
    {
        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! You need a name and value to create a level.");

        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'value' => 10,
                'user' => $user
            ])
        );
    }

    public function testCannotCreateLevelWithoutValue()
    {
        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! You need a name and value to create a level.");

        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => "First",
                'description' => "novice level of the skill",
                'user' => $user
            ])
        );
    }

    public function testCannotCreateLevelWithoutVId()
    {
        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! A valid level collection id is required to create a level.");

        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => "First",
                'value' => 1,
                'description' => "novice level of the skill",
                'user' => $user
            ])
        );
    }

    public function testCannotCreateLevelWithANameAlreadyExisting()
    {
        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! A level with name Last already exists on this collection.");

        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best levels',
                'value' => 10,
                'user' => $user
            ])
        );

        (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'description' => 'most experienced in this skill',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'Last',
            'description' => 'most experienced in this skill',
            'value' => 10,
        ]);
        
        (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'description' => 'most experienced in this skill',
                'value' => 5,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseMissing('levels', [
            'name' => 'Last',
            'description' => 'most experienced in this skill',
            'value' => 5,
        ]);
    }

    public function testCannotCreateLevelWithValueBelowMinimum()
    {
        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best levels',
                'value' => 10,
                'user' => $user
            ])
        );

        $min = Level::MINVALUE;
        $max = $levelCollection->value;

        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! A level's value for this collection should be between {$min} and {$max}.");

        (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'First',
                'description' => 'most experienced in this skill',
                'value' => 0,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseMissing('levels', [
            'name' => 'First',
            'description' => 'most experienced in this skill',
            'value' => 0,
        ]);
    }

    public function testCannotCreateLevelWithValueBelowMaximum()
    {
        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best levels',
                'value' => 10,
                'user' => $user
            ])
        );

        $min = Level::MINVALUE;
        $max = $levelCollection->value;

        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! A level's value for this collection should be between {$min} and {$max}.");

        (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'First',
                'description' => 'most experienced in this skill',
                'value' => $levelCollection->value + 1,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseMissing('levels', [
            'name' => 'First',
            'description' => 'most experienced in this skill',
            'value' => 0,
        ]);
    }

    public function testCanCreateLevelAsFacilitator()
    {
        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best levels',
                'value' => 10,
                'user' => $user
            ])
        );

        (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'description' => 'most experienced in this skill',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'Last',
            'description' => 'most experienced in this skill',
            'value' => 10,
        ]);
    }

    public function testCanCreateLevelAsAdmin()
    {
        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best levels',
                'value' => 10,
                'user' => $user
            ])
        );

        (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'description' => 'highest form of experience',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'Last',
            'description' => 'highest form of experience',
            'value' => 10,
        ]);
    }

    public function testCannotUpdateLevelWithoutUser()
    {
        $this->expectException(LevelException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');

        (new LevelService)->updateLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
            ])
        );
    }

    public function testCannotUpdateLevelWithoutBeingAnAdminOrCreator()
    {
        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to update the level.");

        $user = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();
        $creator = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'value' => 10,
                'user' => $creator,
                'levelCollectionId' => LevelCollection::factory([
                    'name' => 'Best Levels', 
                    'value' => 10,
                    'user_id' => $creator->id
                ])->create()->id
            ])
        );

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Last', 
                'value' => 10,
        ]);

        (new LevelService)->updateLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'value' => 9,
                'levelId' => $level->id,
                'user' => $user
            ])
        );

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Best Levels', 
                'value' => 10,
        ]);
    }

    public function testCannotUpdateLevelWithoutData()
    {
        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! You need a name, value or description to update the level.");

        $user = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => LevelCollection::factory([
                    'name' => 'Best Levels', 
                    'value' => 10,
                    'user_id' => $user->id
                ])->create()->id
            ])
        );

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Last', 
                'value' => 10,
        ]);

        (new LevelService)->updateLevel(
            LevelDTO::new()->fromArray([
                'user' => $user,
                'levelId' => $level->id
            ])
        );
    }

    public function testCannotUpdateLevelWithoutValidId()
    {
        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! You need a valid level to perform this action.");

        $user = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => LevelCollection::factory([
                    'name' => 'Best Levels', 
                    'value' => 10,
                    'user_id' => $user->id
                ])->create()->id
            ])
        );

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Last', 
                'value' => 10,
        ]);

        (new LevelService)->updateLevel(
            LevelDTO::new()->fromArray([
                'user' => $user,
                'name' => 'Best',
            ])
        );
    }

    public function testCannotUpdateLevelToANameAlreadyExisting()
    {
        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! A level with name First already exists on this collection.");

        $user = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();

        $levelCollection = LevelCollection::factory([
            'name' => 'Best Levels', 
            'value' => 10,
            'user_id' => $user->id
        ])->create();

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );
        
        (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'First',
                'value' => 1,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'Last', 
            'value' => 10,
        ]);

        $this->assertDatabaseHas('levels', [
            'name' => 'First', 
            'value' => 1,
        ]);

        (new LevelService)->updateLevel(
            LevelDTO::new()->fromArray([
                'user' => $user,
                'levelId' => $level->id,
                'name' => 'First',
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'Last', 
            'value' => 10,
        ]);

        $this->assertDatabaseHas('levels', [
            'name' => 'First', 
            'value' => 1,
        ]);
    }

    public function testCannotUpdateLevelWithValueBelowMinimum()
    {
        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best levels',
                'value' => 10,
                'user' => $user
            ])
        );

        $min = Level::MINVALUE;
        $max = $levelCollection->value;

        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! A level's value for this collection should be between {$min} and {$max}.");

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'First',
                'description' => 'most experienced in this skill',
                'value' => 1,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'First',
            'description' => 'most experienced in this skill',
            'value' => 1,
        ]);

        (new LevelService)->updateLevel(
            LevelDTO::new()->fromArray([
                'value' => 0,
                'user' => $user,
                'levelId' => $level->id
            ])
        );
    }

    public function testCannotUpdateLevelWithValueBelowMaximum()
    {
        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best levels',
                'value' => 10,
                'user' => $user
            ])
        );

        $min = Level::MINVALUE;
        $max = $levelCollection->value;

        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! A level's value for this collection should be between {$min} and {$max}.");

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'First',
                'description' => 'most experienced in this skill',
                'value' => 1,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'First',
            'description' => 'most experienced in this skill',
            'value' => 1,
        ]);

        (new LevelService)->updateLevel(
            LevelDTO::new()->fromArray([
                'value' => $levelCollection->value + 1,
                'user' => $user,
                'levelId' => $level->id
            ])
        );
    }

    public function testCanUpdateLevelAsCreatorWithName()
    {
        $user = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => LevelCollection::factory([
                    'name' => 'Best Levels', 
                    'value' => 10,
                    'user_id' => $user->id
                ])->create()->id
            ])
        );

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Last', 
                'value' => 10,
        ]);

        (new LevelService)->updateLevel(
            LevelDTO::new()->fromArray([
                'user' => $user,
                'levelId' => $level->id,
                'name' => 'Best',
            ])
        );

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Best', 
                'value' => 10,
        ]);
    }

    public function testCanUpdateLevelAsCreatorWithDescription()
    {
        $user = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => LevelCollection::factory([
                    'name' => 'Best Levels', 
                    'value' => 10,
                    'user_id' => $user->id
                ])->create()->id
            ])
        );

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Last', 
                'description' => null,
                'value' => 10,
        ]);

        (new LevelService)->updateLevel(
            LevelDTO::new()->fromArray([
                'user' => $user,
                'levelId' => $level->id,
                'description' => 'this is the best of the best',
            ])
        );

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Last', 
                'description' => 'this is the best of the best',
                'value' => 10,
        ]);
    }

    public function testCanUpdateLevelAsCreatorWithValue()
    {
        $user = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => LevelCollection::factory([
                    'name' => 'Best Levels', 
                    'value' => 10,
                    'user_id' => $user->id
                ])->create()->id
            ])
        );

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Last', 
                'description' => null,
                'value' => 10,
        ]);

        (new LevelService)->updateLevel(
            LevelDTO::new()->fromArray([
                'user' => $user,
                'levelId' => $level->id,
                'value' => 9,
            ])
        );

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Last', 
                'value' => 9,
        ]);
    }

    public function testCanUpdateLevelAsAdminAndNotCreator()
    {
        $user = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();
        $admin = User::factory()->hasAttached(UserType::factory(['name'=> UserType::ADMIN]))->create();

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => LevelCollection::factory([
                    'name' => 'Best Levels', 
                    'value' => 10,
                    'user_id' => $user->id
                ])->create()->id
            ])
        );

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Last', 
                'description' => null,
                'value' => 10,
        ]);

        (new LevelService)->updateLevel(
            LevelDTO::new()->fromArray([
                'user' => $admin,
                'levelId' => $level->id,
                'value' => 9,
                'description' => 'this is the best of the best',
            ])
        );

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Last', 
                'description' => 'this is the best of the best',
                'value' => 9,
        ]);
    }

    public function testCannotDeleteLevelWithoutUser()
    {
        $this->expectException(LevelException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');

        (new LevelService)->deleteLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
            ])
        );
    }

    public function testCannotDeleteLevelWithoutBeingAnAdminOrCreator()
    {
        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to update the level.");

        $user = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();
        $creator = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'value' => 10,
                'user' => $creator,
                'levelCollectionId' => LevelCollection::factory([
                    'name' => 'Best Levels', 
                    'value' => 10,
                    'user_id' => $creator->id
                ])->create()->id
            ])
        );

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Last', 
                'value' => 10,
        ]);

        (new LevelService)->deleteLevel(
            LevelDTO::new()->fromArray([
                'levelId' => $level->id,
                'user' => $user
            ])
        );

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Best Levels', 
                'value' => 10,
        ]);
    }

    public function testCannotDeleteLevelWithoutValidId()
    {
        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! You need a valid level to perform this action.");

        $user = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => LevelCollection::factory([
                    'name' => 'Best Levels', 
                    'value' => 10,
                    'user_id' => $user->id
                ])->create()->id
            ])
        );

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Last', 
                'value' => 10,
        ]);

        (new LevelService)->deleteLevel(
            LevelDTO::new()->fromArray([
                'levelId' => 10,
                'user' => $user,
                'name' => 'Best',
            ])
        );
    }

    public function testCanDeleteLevelAsAdminAndRemoveFromJobUserSkills()
    {
        $user = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();
        $admin = User::factory()->hasAttached(UserType::factory(['name'=> UserType::ADMIN]))->create();

        $levelCollection = LevelCollection::factory([
            'name' => 'Best Levels', 
            'value' => 10,
            'user_id' => $user->id
        ])->create();

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $level2 = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'First',
                'value' => 1,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );
        
        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'addedBy' => $user,
            ])
        );
        
        $skillType = SkillType::factory()->create([
            'name' => 'Programming Language',
            'user_id' => $user
        ]);

        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for backend',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );
        
        $skill2 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP8',
                'description' => 'for backend',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $jobUser = $user->jobUsers()->create(['job_id' => $job->id]);
        $jobUser->jobUserSkills()->create([
            'level_id' => $level->id,
            'skill_id' => $skill->id
        ]);
        $jobUser->jobUserSkills()->create([
            'level_id' => $level2->id,
            'skill_id' => $skill2->id
        ]);

        $this->assertDatabaseHas('levels', [
            'name' => 'Last', 
            'value' => 10,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser->id, 
            'skill_id' => $skill->id,
            'level_id' => $level->id,
        ]);

        $result = (new LevelService)->deleteLevel(
            LevelDTO::new()->fromArray([
                'levelId' => $level->id,
                'user' => $admin,
            ])
        );

        $this->assertDatabaseMissing('levels', [
            'name' => 'Last', 
            'value' => 10,
        ]);

        $this->assertTrue((bool) $result);

        $this->assertDatabaseMissing('job_user_skill', [
            'job_user_id' => $jobUser->id, 
            'skill_id' => $skill->id,
            'level_id' => $level->id,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser->id, 
            'skill_id' => $skill->id,
            'level_id' => null,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser->id, 
            'skill_id' => $skill2->id,
            'level_id' => $level2->id,
        ]);
    }

    public function testCannotDeleteLevelAsCreatorAndRemoveFromOwnJobUserSkills()
    {
        $user = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();
        $otherUser = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();

        $levelCollection = LevelCollection::factory([
            'name' => 'Best Levels', 
            'value' => 10,
            'user_id' => $user->id
        ])->create();

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $level2 = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'First',
                'value' => 1,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );
        
        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'addedBy' => $user,
            ])
        );
        
        $skillType = SkillType::factory()->create([
            'name' => 'Programming Language',
            'user_id' => $user
        ]);

        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for backend',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );
        
        $skill2 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP8',
                'description' => 'for backend',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $jobUser = $user->jobUsers()->create(['job_id' => $job->id]);
        $jobUser->jobUserSkills()->create([
            'level_id' => $level->id,
            'skill_id' => $skill->id
        ]);
        $jobUser->jobUserSkills()->create([
            'level_id' => $level2->id,
            'skill_id' => $skill2->id
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser->id, 
            'skill_id' => $skill->id,
            'level_id' => $level->id,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser->id, 
            'skill_id' => $skill2->id,
            'level_id' => $level2->id,
        ]);

        $jobUser2 = $otherUser->jobUsers()->create(['job_id' => $job->id]);
        $jobUser2->jobUserSkills()->create([
            'level_id' => $level->id,
            'skill_id' => $skill->id
        ]);

        $this->assertDatabaseHas('levels', [
            'name' => 'Last', 
            'value' => 10,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser2->id, 
            'skill_id' => $skill->id,
            'level_id' => $level->id,
        ]);

        $result = (new LevelService)->deleteLevel(
            LevelDTO::new()->fromArray([
                'levelId' => $level->id,
                'user' => $user,
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'Last', 
            'value' => 10,
        ]);

        $this->assertTrue((bool) $result);

        $this->assertDatabaseMissing('job_user_skill', [
            'job_user_id' => $jobUser->id, 
            'skill_id' => $skill->id,
            'level_id' => $level->id,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser->id, 
            'skill_id' => $skill->id,
            'level_id' => null,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser->id, 
            'skill_id' => $skill2->id,
            'level_id' => $level2->id,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser2->id, 
            'skill_id' => $skill->id,
            'level_id' => $level->id,
        ]);
    }

    public function testCanDeleteLevelAsCreatorAndRemoveFromOwnJobUserSkillsIfNoOtherJobUserSkillHasLevel()
    {
        $user = User::factory()->hasAttached(UserType::factory(['name'=> UserType::FACILITATOR]))->create();

        $levelCollection = LevelCollection::factory([
            'name' => 'Best Levels', 
            'value' => 10,
            'user_id' => $user->id
        ])->create();

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $level2 = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'First',
                'value' => 1,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );
        
        $job = (new JobService)->createJob(
            JobDTO::new()->fromArray([
                'name' => 'Web Developer',
                'addedBy' => $user,
            ])
        );
        
        $skillType = SkillType::factory()->create([
            'name' => 'Programming Language',
            'user_id' => $user
        ]);

        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for backend',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );
        
        $skill2 = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP8',
                'description' => 'for backend',
                'user' => $user,
                'skillTypeId' => $skillType->id
            ])
        );

        $jobUser = $user->jobUsers()->create(['job_id' => $job->id]);
        $jobUser->jobUserSkills()->create([
            'level_id' => $level->id,
            'skill_id' => $skill->id
        ]);
        $jobUser->jobUserSkills()->create([
            'level_id' => $level2->id,
            'skill_id' => $skill2->id
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser->id, 
            'skill_id' => $skill->id,
            'level_id' => $level->id,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser->id, 
            'skill_id' => $skill2->id,
            'level_id' => $level2->id,
        ]);

        $this->assertDatabaseHas('levels', [
            'name' => 'Last', 
            'value' => 10,
        ]);

        $result = (new LevelService)->deleteLevel(
            LevelDTO::new()->fromArray([
                'levelId' => $level->id,
                'user' => $user,
            ])
        );

        $this->assertDatabaseMissing('levels', [
            'name' => 'Last', 
            'value' => 10,
        ]);

        $this->assertTrue((bool) $result);

        $this->assertDatabaseMissing('job_user_skill', [
            'job_user_id' => $jobUser->id, 
            'skill_id' => $skill->id,
            'level_id' => $level->id,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser->id, 
            'skill_id' => $skill->id,
            'level_id' => null,
        ]);

        $this->assertDatabaseHas('job_user_skill', [
            'job_user_id' => $jobUser->id, 
            'skill_id' => $skill2->id,
            'level_id' => $level2->id,
        ]);
    }
}
