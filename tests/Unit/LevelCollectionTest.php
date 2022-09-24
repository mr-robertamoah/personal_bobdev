<?php

namespace Tests\Unit;

use App\DTOs\LevelCollectionDTO;
use App\Exceptions\LevelCollectionException;
use App\Models\LevelCollection;
use App\Models\User;
use App\Models\UserType;
use App\Services\LevelCollectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LevelCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateLevelCollectionWithoutUser()
    {
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');

        (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
            ])
        );
    }

    public function testCannotCreateLevelCollectionWithoutBeingAnAdminOrFacilitator()
    {
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to create a level collection.");

        $user = User::factory()->create();

        (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user
            ])
        );
    }

    public function testCannotCreateLevelCollectionWithoutName()
    {
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage("Sorry! You need a name and value to create a level collection.");

        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'value' => 10,
                'user' => $user
            ])
        );
    }

    public function testCannotCreateLevelCollectionWithoutDescription()
    {
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage("Sorry! You need a name and value to create a level collection.");

        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => "Best Levels",
                'user' => $user
            ])
        );
    }

    public function testCanCreateLevelCollectionAsFacilitator()
    {
        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels',
            'value' => 10,
        ]);
    }

    public function testCanCreateLevelCollectionAsAdmin()
    {
        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels',
            'value' => 10,
        ]);
    }

    public function testCannotUpdateLevelCollectionWithoutUser()
    {
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');

        (new LevelCollectionService)->updateLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
            ])
        );
    }

    public function testCannotUpdateLevelCollectionWithoutBeingAnAdminOrFacilitator()
    {
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to update a level collection.");

        $user = User::factory()->create();

        (new LevelCollectionService)->updateLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user
            ])
        );
    }

    public function testCannotUpdateLevelCollectionIfFacilitatorAndNotCreator()
    {
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to update a level collection.");

        $user = User::factory()->hasAttached(
            UserType::factory(['name' => UserType::FACILITATOR]), [], 'userTypes'
        )->create();
        
        $facilitator = User::factory()->hasAttached(
            UserType::factory(['name' => UserType::FACILITATOR]), [], 'userTypes'
        )->create();

        $LevelCollection = LevelCollection::factory([
            'name' => 'Best Levels', 
            'value' => 5, 
            'user_id' => $user->id
        ])->create();

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels', 
            'value' => 5
        ]);

        (new LevelCollectionService)->updateLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $facilitator,
                'levelCollectionId' => $LevelCollection->id
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels', 
            'value' => 5
        ]);
    }

    public function testCannotUpateLevelCollectionWithoutNameAndDescription()
    {
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage("Sorry! You need a name and value to update a level collection.");

        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        $LevelCollection = LevelCollection::factory([
            'name' => 'Best Levels', 
            'value' => 5, 
            'user_id' => $user->id
        ])->create();

        (new LevelCollectionService)->updateLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'user' => $user,
                'levelCollectionId' => $LevelCollection->id
            ])
        );
    }

    public function testCannotUpdateLevelCollectionWithoutId()
    {
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage("Sorry! You need a valid level collection to perform this action.");

        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        (new LevelCollectionService)->updateLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => "Best Levels",
                'value' => 10,
                'user' => $user
            ])
        );
    }

    public function testCanUpdateLevelCollectionIfFacilitatorAndCreator()
    {
        $user = User::factory()->hasAttached(
            UserType::factory(['name' => UserType::FACILITATOR]), [], 'userTypes'
        )->create();

        $LevelCollection = LevelCollection::factory([
            'name' => 'Best Levels', 
            'value' => 5, 
            'user_id' => $user->id
        ])->create();

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels', 
            'value' => 5
        ]);

        (new LevelCollectionService)->updateLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => $LevelCollection->id
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels', 
            'value' => 10
        ]);
    }

    public function testCanUpdateLevelCollectionIfAdminAndNotCreator()
    {
        $user = User::factory()->hasAttached(
            UserType::factory(['name' => UserType::FACILITATOR]), [], 'userTypes'
        )->create();
        
        $admin = User::factory()->hasAttached(
            UserType::factory(['name' => UserType::ADMIN]), [], 'userTypes'
        )->create();

        $LevelCollection = LevelCollection::factory([
            'name' => 'Best Levels', 
            'value' => 5, 
            'user_id' => $user->id
        ])->create();

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels', 
            'value' => 5
        ]);

        (new LevelCollectionService)->updateLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $admin,
                'levelCollectionId' => $LevelCollection->id
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels', 
            'value' => 10
        ]);
    }
}
