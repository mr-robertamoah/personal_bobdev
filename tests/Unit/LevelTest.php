<?php

namespace Tests\Unit;

use App\DTOs\LevelCollectionDTO;
use App\DTOs\LevelDTO;
use App\Exceptions\LevelException;
use App\Models\User;
use App\Models\UserType;
use App\Services\LevelCollectionService;
use App\Services\LevelService;
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
}
