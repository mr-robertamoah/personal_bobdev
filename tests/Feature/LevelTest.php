<?php

namespace Tests\Feature;

use App\DTOs\LevelCollectionDTO;
use App\DTOs\LevelDTO;
use App\Exceptions\LevelException;
use App\Models\User;
use App\Models\UserType;
use App\Services\LevelCollectionService;
use App\Services\LevelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LevelTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateLevelIfNotAnAdminAndFacilitator()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $this->actingAs($user);
        
        $response = $this->post('/api/level/create', [
            'name' => 'Programming Language',
            'description' => 'this is to help create web and native apps'
        ]);

        $response
            ->assertStatus(403);
    }
    
    public function testCannotCreateLevelWithoutAName()
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

        $this->actingAs($user);
        
        $response = $this->postJson('/api/level/create', [
            'description' => 'this is to help create web and native apps',
            'value' => 10
        ]);

        $response
            ->assertStatus(422);
    }
    
    public function testCannotCreateLevelWithoutAValue()
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

        $this->actingAs($user);

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user
            ])
        );
        
        $response = $this->postJson('/api/level/create', [
            'name' => 'Best Levels',
            'levelCollectionId' => $levelCollection->id
        ]);

        $response
            ->assertStatus(422);
    }
    
    public function testCannotCreateLevelWithoutALevelCollectionId()
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

        $this->actingAs($user);

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user
            ])
        );
        
        $response = $this->postJson('/api/level/create', [
            'name' => 'Best Levels',
            'value' => 10
        ]);

        $response
            ->assertStatus(422);
    }
    
    public function testCanCreateLevelCollectionIfAdmin()
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

        $this->actingAs($user);

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user
            ])
        );

        $response = $this->postJson('/api/level/create', [
            'name' => 'Best Levels',
            'value' => 10,
            'level_collection_id' => $levelCollection->id
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'level' => [
                    'name' => 'Best Levels',
                    'value' => 10
                ]
            ]);

        $this->assertDatabaseHas('levels', [
            'name' => 'Best Levels',
            'value' => 10
        ]);

        $this->assertDatabaseHas('activities', [
            'performedon_type' => 'App\Models\Level',
            'performedon_id' => 1,
            'action' => 'create',
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
        ]);
    }
    
    public function testCanCreateLevelIfFacilitator()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $facilitator = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $facilitator->userTypes()->attach($userType->id);

        $this->actingAs($facilitator);

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user
            ])
        );

        $response = $this->postJson('/api/level/create', [
            'name' => 'Best Levels',
            'value' => 10,
            'level_collection_id' => $levelCollection->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'level' => [
                    'name' => 'Best Levels',
                    'value' => 10
                ]
            ]);

        $this->assertDatabaseHas('levels', [
            'name' => 'Best Levels',
            'value' => 10
        ]);

        $this->assertDatabaseMissing('activities', [
            'performedon_type' => 'App\Models\Skill',
            'performedon_id' => 1,
            'action' => 'create',
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $facilitator->id,
        ]);
    }
    
    public function testCanUpdateLevelIfAnAdmin()
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

        $this->actingAs($user);

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user
            ])
        );

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Last',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'Last',
            'value' => 10
        ]);
        
        $response = $this->post("/api/level/{$level->id}/update", [
            'name' => 'Last Level',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'level' => [
                    'name' => 'Last Level',
                    'value' => 10,
                ]
            ]);

        $this->assertDatabaseHas('levels', [
            'name' => 'Last Level',
            'value' => 10,
        ]);
        
        $this->assertDatabaseHas('activities', [
            'performedon_type' => 'App\Models\Level',
            'performedon_id' => $response->getData()->level->id,
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
            'action' => 'update'
        ]);
    }
    
    public function testCanUpdateLevelIfACreatorAndFacilitator()
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

        $this->actingAs($user);

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user
            ])
        );

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Best',
                'value' => 10,
                'levelCollectionId' => $levelCollection->id,
                'user' => $user
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'Best',
        ]);
        
        $response = $this->post("/api/level/{$level->id}/update", [
            'name' => 'Best Level',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'level' => [
                    'name' => 'Best Level',
                    'value' => 10
                ]
            ]);

        $this->assertDatabaseHas('levels', [
            'name' => 'Best Level',
        ]);

        $this->assertDatabaseMissing('activities', [
            'performedon_type' => 'App\Models\Level',
            'performedon_id' => $level->id,
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
            'action' => 'delete'
        ]);
    }
    
    public function testCannotUpdateLevelIfAFacilitatorAndNotCreator()
    {
        $this->expectException(LevelException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to update the level.");

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

        $user2 = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $user2->userTypes()->attach($userType->id);

        $this->actingAs($user2);

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user
            ])
        );

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Best Level',
                'value' => 10,
                'levelCollectionId' => $levelCollection->id,
                'user' => $user
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels',
        ]);

        $this->assertDatabaseHas('levels', [
            'name' => 'Best Level',
        ]);
        
        $response = $this->post("/api/level/{$level->id}/update", [
            'name' => 'Only Level',
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'errors'
            ]);

        $this->assertDatabaseHas('levels', [
            'name' => 'Best Level',
        ]);
    }
    
    public function testCanDeleteLevelIfAnAdmin()
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

        $this->actingAs($user);

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user
            ])
        );

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Best Level',
                'value' => 10,
                'user' => $user,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'Best Level',
            'value' => 10
        ]);

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels',
            'value' => 10
        ]);
        
        $response = $this->delete("/api/level/{$level->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);

        $this->assertDatabaseMissing('levels', [
            'name' => 'Best Level',
            'value' => 10
        ]);

        $this->assertDatabaseHas('activities', [
            'performedon_type' => 'App\Models\Level',
            'performedon_id' => $level->id,
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
            'action' => 'delete'
        ]);
    }
    
    public function testCanDeleteLevelIfAFacilitatorAndCreator()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $user2 = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $user->userTypes()->attach($userType->id);

        $userType = $user->addedUserTypes()->create([
            'name' => UserType::FACILITATOR
        ]);

        $user2->userTypes()->attach($userType->id);

        $this->actingAs($user2);

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $user,
            ])
        );

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Best Level',
                'user' => $user2,
                'value' => 10,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'Best Level',
            'value' => 10
        ]);

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels',
            'value' => 10
        ]);
        
        $response = $this->deleteJson("/api/level/{$level->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);

        $this->assertDatabaseMissing('levels', [
            'name' => 'Best Level',
            'value' => 10
        ]);

        $this->assertDatabaseMissing('activities', [
            'performedon_type' => 'App\Models\Level',
            'performedon_id' => $level->id,
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
            'action' => 'delete'
        ]);
    }

    public function testCanGetLevelWithLevelId()
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

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $admin
            ])
        );

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Programming Language',
                'user' => $admin,
                'value' => 10,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'Programming Language',
        ]);

        $this->actingAs($user);

        $response = $this->get("/api/level/?id={$level->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'level' => [
                    'name' => 'Programming Language',
                    'value' => 10
                ]
            ]);
    }

    public function testCanGetLevelWithLevelName()
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

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $admin
            ])
        );

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Programming Language',
                'user' => $admin,
                'value' => 10,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'Programming Language',
            'value' => 10
        ]);

        $this->actingAs($user);

        $response = $this->get("/api/level?name={$level->name}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'level' => [
                    'name' => 'Programming Language',
                    'value' => 10
                ]
            ]);
    }

    public function testCanGetLevelsWithLevelName()
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

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Best Levels',
                'value' => 10,
                'user' => $admin
            ])
        );

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Programming Language',
                'user' => $admin,
                'value' => 10,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'Programming Language',
            'value' => 10
        ]);

        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => 'Designing Language',
                'user' => $admin,
                'value' => 10,
                'levelCollectionId' => $levelCollection->id
            ])
        );

        $this->assertDatabaseHas('levels', [
            'name' => 'Designing Language',
            'value' => 10
        ]);

        $this->actingAs($user);

        $response = $this->get("/api/levels?name=language");

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['name' => 'Programming Language'],
                    ['name' => 'Designing Language'],
                ]
            ]);
    }
}
