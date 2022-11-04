<?php

namespace Tests\Feature;

use App\DTOs\SkillTypeDTO;
use App\Exceptions\SkillTypeException;
use App\Models\User;
use App\Models\UserType;
use App\Services\SkillTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SkillTypeTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateSkillTypeIfNotAnAdminAndFacilitator()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $this->actingAs($user);
        
        $response = $this->post('/api/skill_type/create', [
            'name' => 'Programming Language',
            'description' => 'this is to help create web and native apps'
        ]);

        $response
            ->assertStatus(403);
    }
    
    public function testCannotCreateSkillTypeWithoutAName()
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
        
        $response = $this->postJson('/api/skill_type/create', [
            'description' => 'this is to help create web and native apps'
        ]);

        $response
            ->assertStatus(422);
    }
    
    public function testCanCreateSkillTypeIfAnAdmin()
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
        
        $response = $this->post('/api/skill_type/create', [
            'name' => 'Programming Language',
            'description' => 'this is to help create web and native apps'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'skillType' => [
                    'name' => 'Programming Language',
                    'description' => 'this is to help create web and native apps'
                ]
            ]);
        
        $this->assertDatabaseHas('activities', [
            'performedon_type' => 'App\Models\SkillType',
            'performedon_id' => $response->getData()->skillType->id,
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
            'action' => 'create'
        ]);
    }
    
    public function testCanCreateSkillTypeIfAFacilitator()
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
        
        $response = $this->post('/api/skill_type/create', [
            'name' => 'Programming Language',
            'description' => 'this is to help create web and native apps'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'skillType' => [
                    'name' => 'Programming Language',
                    'description' => 'this is to help create web and native apps'
                ]
            ]);
    }
    
    public function testCanUpdateSkillTypeIfAnAdmin()
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

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Framework',
                'addedBy' => $user
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Framework',
        ]);
        
        $response = $this->post("/api/skill_type/{$skillType->id}/update", [
            'name' => 'Programming Language',
            'description' => 'this is to help create web and native apps'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'skillType' => [
                    'name' => 'Programming Language',
                    'description' => 'this is to help create web and native apps'
                ]
            ]);

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
            'description' => 'this is to help create web and native apps'
        ]);
        
        $this->assertDatabaseHas('activities', [
            'performedon_type' => 'App\Models\SkillType',
            'performedon_id' => $response->getData()->skillType->id,
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
            'action' => 'update'
        ]);
    }
    
    public function testCanUpdateSkillTypeIfACreatorAndFacilitator()
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

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Framework',
                'addedBy' => $user
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Framework',
        ]);
        
        $response = $this->post("/api/skill_type/{$skillType->id}/update", [
            'name' => 'Programming Language',
            'description' => 'this is to help create web and native apps'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'skillType' => [
                    'name' => 'Programming Language',
                    'description' => 'this is to help create web and native apps'
                ]
            ]);

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
            'description' => 'this is to help create web and native apps'
        ]);

        $this->assertDatabaseMissing('activities', [
            'performedon_type' => 'App\Models\SkillType',
            'performedon_id' => $skillType->id,
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
            'action' => 'delete'
        ]);
    }
    
    public function testCannotUpdateSkillTypeIfAFacilitatorAndNotCreator()
    {
        $this->expectException(SkillTypeException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to update the skill type with name Framework.");

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

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Framework',
                'addedBy' => $user
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Framework',
        ]);
        
        $response = $this->post("/api/skill_type/{$skillType->id}/update", [
            'name' => 'Programming Language',
            'description' => 'this is to help create web and native apps'
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'errors'
            ]);

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Framework',
        ]);
    }
    
    public function testCanDeleteSkillTypeIfAnAdmin()
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

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Framework',
                'addedBy' => $user
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Framework',
        ]);
        
        $response = $this->delete("/api/skill_type/{$skillType->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);

        $this->assertSoftDeleted('skill_types', [
            'name' => 'Framework',
        ]);

        $this->assertDatabaseHas('activities', [
            'performedon_type' => 'App\Models\SkillType',
            'performedon_id' => $skillType->id,
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
            'action' => 'delete'
        ]);
    }
    
    public function testCanDeleteSkillTypeIfAFacilitatorAndCreator()
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

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Framework',
                'addedBy' => $user2
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Framework',
        ]);
        
        $response = $this->deleteJson("/api/skill_type/{$skillType->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);

        $this->assertSoftDeleted('skill_types', [
            'name' => 'Framework',
        ]);

        $this->assertDatabaseMissing('activities', [
            'performedon_type' => 'App\Models\SkillType',
            'performedon_id' => $skillType->id,
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
            'action' => 'delete'
        ]);
    }

    public function testCanGetSkillTypeWithSkillTypeId()
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

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'addedBy' => $admin
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
        ]);

        $this->actingAs($user);

        $response = $this->get("/api/skill_type/?id={$skillType->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'skillType' => [
                    'name' => 'Programming Language',
                ]
            ]);
    }

    public function testCanGetSkillTypeWithSkillTypeName()
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

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'addedBy' => $admin
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
        ]);

        $this->actingAs($user);

        $response = $this->get("/api/skill_type?name={$skillType->name}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'skillType' => [
                    'name' => 'Programming Language',
                ]
            ]);
    }

    public function testCanGetSkillTypesWithSkillTypeName()
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

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'addedBy' => $admin
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
        ]);

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Designing Language',
                'addedBy' => $admin
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Designing Language',
        ]);

        $this->actingAs($user);

        $response = $this->get("/api/skill_types?name=language");

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
