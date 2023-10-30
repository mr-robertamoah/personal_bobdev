<?php

namespace Tests\Feature;

use App\DTOs\LevelCollectionDTO;
use App\Exceptions\LevelCollectionException;
use App\Models\User;
use App\Models\UserType;
use App\Services\LevelCollectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LevelCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateLevelCollectionIfNotAnAdminAndFacilitator()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $this->actingAs($user);
        
        $response = $this->post('/api/level_collection', [
            'name' => 'Programming Language',
            'description' => 'this is to help create web and native apps'
        ]);

        $response
            ->assertStatus(403);
    }
    
    public function testCannotCreateLevelCollectionWithoutAName()
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
        
        $response = $this->postJson('/api/level_collection', [
            'description' => 'this is to help create web and native apps',
            'value' => 10
        ]);

        $response
            ->assertStatus(422);
    }
    
    public function testCannotCreateLevelCollectionWithoutAValue()
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
        
        $response = $this->postJson('/api/level_collection', [
            'name' => 'Best Levels'
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

        $response = $this->postJson('/api/level_collection', [
            'name' => 'Best Levels',
            'value' => 10
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'levelCollection' => [
                    'name' => 'Best Levels',
                    'value' => 10
                ]
            ]);

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels',
            'value' => 10
        ]);

        $this->assertDatabaseHas('activities', [
            'performedon_type' => 'App\Models\LevelCollection',
            'performedon_id' => 1,
            'action' => 'create',
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
        ]);
    }
    
    public function testCanCreateLevelCollectionIfFacilitator()
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

        $response = $this->postJson('/api/level_collection', [
            'name' => 'Best Levels',
            'value' => 10
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'levelCollection' => [
                    'name' => 'Best Levels',
                    'value' => 10
                ]
            ]);

        $this->assertDatabaseHas('level_collections', [
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
    
    public function testCanCreateLevelCollectionAndLevelsIfFacilitator()
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

        $response = $this->postJson('/api/level_collection', [
            'name' => 'Best Levels',
            'value' => 10,
            'levels' => [
                ['name' => 'First', 'value' => 1],
                ['name' => 'Second', 'value' => 2],
            ]
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'levelCollection' => [
                    'name' => 'Best Levels',
                    'value' => 10,
                    'levelsCount' => 2
                ]
            ]);

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels',
            'value' => 10
        ]);

        $this->assertDatabaseHas('levels', [
            'name' => 'First',
            'value' => 1
        ]);

        $this->assertDatabaseHas('levels', [
            'name' => 'Second',
            'value' => 2
        ]);

        $this->assertDatabaseMissing('activities', [
            'performedon_type' => 'App\Models\Skill',
            'performedon_id' => 1,
            'action' => 'create',
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $facilitator->id,
        ]);
    }
    
    public function testCanUpdateLevelCollectionIfAnAdmin()
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

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels',
            'value' => 10
        ]);
        
        $response = $this->post("/api/level_collection/{$levelCollection->id}", [
            'name' => 'Only Level',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'levelCollection' => [
                    'name' => 'Only Level',
                    'value' => 10,
                ]
            ]);

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Only Level',
            'value' => 10,
        ]);
        
        $this->assertDatabaseHas('activities', [
            'performedon_type' => 'App\Models\LevelCollection',
            'performedon_id' => $response->getData()->levelCollection->id,
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
            'action' => 'update'
        ]);
    }
    
    public function testCanUpdateLevelCollectionIfACreatorAndFacilitator()
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

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels',
        ]);
        
        $response = $this->post("/api/level_collection/{$levelCollection->id}", [
            'name' => 'Only Level',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'levelCollection' => [
                    'name' => 'Only Level',
                    'value' => 10
                ]
            ]);

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Only Level',
        ]);

        $this->assertDatabaseMissing('activities', [
            'performedon_type' => 'App\Models\LevelCollection',
            'performedon_id' => $levelCollection->id,
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
            'action' => 'delete'
        ]);
    }
    
    public function testCannotUpdateLevelCollectionIfAFacilitatorAndNotCreator()
    {
        $this->expectException(LevelCollectionException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to update the level collection.");

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

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels',
        ]);
        
        $response = $this->post("/api/level_collection/{$levelCollection->id}", [
            'name' => 'Only Level',
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'errors'
            ]);

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Best Levels',
        ]);
    }
    
    public function testCanDeleteLevelCollectionIfAnAdmin()
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
                'name' => 'Framework',
                'value' => 10,
                'user' => $user
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Framework',
            'value' => 10
        ]);
        
        $response = $this->delete("/api/level_collection/{$levelCollection->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);

        $this->assertDatabaseMissing('level_collections', [
            'name' => 'Framework',
            'value' => 10
        ]);

        $this->assertDatabaseHas('activities', [
            'performedon_type' => 'App\Models\LevelCollection',
            'performedon_id' => $levelCollection->id,
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
            'action' => 'delete'
        ]);
    }
    
    public function testCanDeleteLevelCollectionIfAFacilitatorAndCreator()
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
                'name' => 'Framework',
                'user' => $user2,
                'value' => 10
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Framework',
            'value' => 10
        ]);
        
        $response = $this->deleteJson("/api/level_collection/{$levelCollection->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);

        $this->assertDatabaseMissing('level_collections', [
            'name' => 'Framework',
            'value' => 10
        ]);

        $this->assertDatabaseMissing('activities', [
            'performedon_type' => 'App\Models\LevelCollection',
            'performedon_id' => $levelCollection->id,
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
            'action' => 'delete'
        ]);
    }

    public function testCanGetLevelCollectionWithLevelCollectionId()
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
                'name' => 'Programming Language',
                'user' => $admin,
                'value' => 10
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Programming Language',
        ]);

        $this->actingAs($user);

        $response = $this->get("/api/level_collection/?id={$levelCollection->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'levelCollection' => [
                    'name' => 'Programming Language',
                    'value' => 10
                ]
            ]);
    }

    public function testCanGetLevelCollectionWithLevelCollectionName()
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
                'name' => 'Programming Language',
                'user' => $admin,
                'value' => 10
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Programming Language',
            'value' => 10
        ]);

        $this->actingAs($user);

        $response = $this->get("/api/level_collection?name={$levelCollection->name}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'levelCollection' => [
                    'name' => 'Programming Language',
                    'value' => 10
                ]
            ]);
    }

    public function testCanGetLevelCollectionsWithLevelCollectionName()
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
                'name' => 'Programming Language',
                'user' => $admin,
                'value' => 10
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Programming Language',
            'value' => 10
        ]);

        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => 'Designing Language',
                'user' => $admin,
                'value' => 10
            ])
        );

        $this->assertDatabaseHas('level_collections', [
            'name' => 'Designing Language',
            'value' => 10
        ]);

        $this->actingAs($user);

        $response = $this->get("/api/level_collections?name=language");

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
