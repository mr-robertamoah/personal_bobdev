<?php

namespace Tests\Unit;

use App\DTOs\LevelCollectionDTO;
use App\DTOs\LevelDTO;
use App\Exceptions\LevelCollectionException;
use App\Models\Level;
use App\Models\LevelCollection;
use App\Models\User;
use App\Models\UserType;
use App\Services\LevelCollectionService;
use Illuminate\Database\Eloquent\Factories\Sequence;
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

    public function testCannotCreateLevelCollectionWithANameThatAlreadyExists()
    {
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage("Sorry! A level collection already exists with the name Best Levels.");

        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => "Best Levels",
                'value' => 10,
                'user' => $user
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels',
            'value' => 10,
        ]);

        (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => "Best Levels",
                'value' => 10,
                'user' => $user
            ])
        );
    }

    public function testCannotCreateLevelCollectionIfValueIsLowerThanOrEqualToTheLevelMinValue()
    {
        $levelMinValue = Level::MINVALUE;
        
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage("Sorry! The value of the level collection should be greater than {$levelMinValue}.");

        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => "Best Levels",
                'value' => 1,
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

    public function testCanCreateLevelCollectionAndLevelsAsFacilitator()
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
                'user' => $user,
                'levelDTOs' => [
                    LevelDTO::new()->fromArray([
                        'name' => 'First',
                        'value' => 1,
                        'user' => $user
                    ]),
                    LevelDTO::new()->fromArray([
                        'name' => 'Second',
                        'value' => 2,
                        'user' => $user
                    ]),
                ]
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels',
            'value' => 10,
        ]);

        $this->assertDatabaseHas('levels', [
            'name' => 'First',
            'value' => 1,
        ]);

        $this->assertDatabaseHas('levels', [
            'name' => 'Second',
            'value' => 2,
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
        $this->expectExceptionMessage("Sorry! You are not authorized to update the level collection.");

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
        $this->expectExceptionMessage("Sorry! You are not authorized to update the level collection.");

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
        $this->expectExceptionMessage("Sorry! You need a name or value to update a level collection.");

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

    public function testCannotUpdateLevelCollectionWithANameOfAnExistingOne()
    {
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage("Sorry! A level collection already exists with the name Just Best.");

        $user = User::factory()
            ->hasAttached(
            UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();

        $LevelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => "Best Levels",
                'value' => 10,
                'user' => $user
            ])
        );

        (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => "Just Best",
                'value' => 10,
                'user' => $user
            ])
        );

        (new LevelCollectionService)->updateLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => "Just Best",
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => $LevelCollection->id
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
                'name' => 'Just Best',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => $LevelCollection->id
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Just Best', 
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
                'name' => 'Just Best',
                'value' => 10,
                'user' => $admin,
                'levelCollectionId' => $LevelCollection->id
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Just Best', 
            'value' => 10
        ]);
    }

    public function testCannotDeleteLevelCollectionWithoutId()
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
                'user' => $user
            ])
        );
    }

    public function testCannotDeleteLevelCollectionWithoutUser()
    {
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');

        (new LevelCollectionService)->deleteLevelCollection(
            LevelCollectionDTO::new()->fromArray([])
        );
    }

    public function testCannotDeleteLevelCollectionWithoutBeingAnAdminAndCreator()
    {
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to delete the level collection.");

        $user = User::factory()->create();

        (new LevelCollectionService)->deleteLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user
            ])
        );
    }

    public function testCannotDeleteLevelCollectionIfFacilitatorAndNotCreator()
    {
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to delete the level collection.");

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

        (new LevelCollectionService)->deleteLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'user' => $facilitator,
                'levelCollectionId' => $LevelCollection->id
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels', 
            'value' => 5
        ]);
    }

    public function testCanDeleteLevelCollectionIfCreator()
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

        (new LevelCollectionService)->deleteLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'user' => $user,
                'levelCollectionId' => $LevelCollection->id
            ])
        );

        $this->assertDatabaseMissing('level_collections', [
            'name' => 'Best Levels', 
            'value' => 5
        ]);
    }

    public function testCanDeleteLevelCollectionIfAdminAndNotCreator()
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

        (new LevelCollectionService)->deleteLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'user' => $admin,
                'levelCollectionId' => $LevelCollection->id
            ])
        );

        $this->assertDatabaseMissing('level_collections', [
            'name' => 'Best Levels', 
            'value' => 5
        ]);
    }

    public function testCanDeleteLevelCollectionAndItsLevelsIfAdminAndNotCreator()
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

        Level::factory()->state(new Sequence(
            [
                'name' => 'First', 
                'value' => 1, 
                'user_id' => $user->id,
                'level_collection_id' => $LevelCollection->id,
            ],
            [
                'name' => 'Second', 
                'value' => 2, 
                'user_id' => $user->id,
                'level_collection_id' => $LevelCollection->id,
            ],
        ))->count(2)->create();

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'First', 
                'value' => 1, 
        ]);

        $this->assertDatabaseHas('levels', 
            [
                'name' => 'Second', 
                'value' => 2, 
        ]);

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels', 
            'value' => 5
        ]);

        (new LevelCollectionService)->deleteLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'user' => $admin,
                'levelCollectionId' => $LevelCollection->id
            ])
        );

        $this->assertDatabaseMissing('level_collections', [
            'name' => 'Best Levels', 
            'value' => 5
        ]);

        $this->assertDatabaseMissing('levels', [
                'name' => 'First', 
                'value' => 1, 
        ]);

        $this->assertDatabaseMissing('levels', 
            [
                'name' => 'Second', 
                'value' => 2, 
        ]);
    }
}
