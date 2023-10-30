<?php

namespace Tests\Feature;

use App\DTOs\SkillDTO;
use App\DTOs\SkillTypeDTO;
use App\Exceptions\SkillException;
use App\Models\User;
use App\Models\UserType;
use App\Services\SkillService;
use App\Services\SkillTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SkillTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateSkillIfNotAnAdminAndFacilitator()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $this->actingAs($user);
        
        $response = $this->post('/api/skill', [
            'name' => 'Programming Language',
            'description' => 'this is to help create web and native apps'
        ]);

        $response
            ->assertStatus(403);
    }
    
    public function testCannotCreateSkillWithoutAName()
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
        
        $response = $this->postJson('/api/skill', [
            'description' => 'this is to help create web and native apps'
        ]);

        $response
            ->assertStatus(422);
    }
    
    public function testCannotCreateSkillWithoutADescription()
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
        
        $response = $this->postJson('/api/skill', [
            'name' => 'Web Developer'
        ]);

        $response
            ->assertStatus(422);
    }
    
    public function testCannotCreateSkillWithoutAValidSkillType()
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
        
        $response = $this->postJson('/api/skill', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);

        $response
            ->assertStatus(500);
    }
    
    public function testCanCreateSkillIfAdmin()
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
                'name' => 'Programming Language',
                'addedBy' => $user
            ])
        );

        $response = $this->postJson('/api/skill', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
            'skill_type_id' => $skillType->id
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'skill' => [
                    'name' => 'PHP',
                    'description' => 'for creating backend solutions for websites',
                ]
            ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);

        $this->assertDatabaseHas('activities', [
            'performedon_type' => 'App\Models\Skill',
            'performedon_id' => 1,
            'action' => 'create',
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
        ]);
    }
    
    public function testCanCreateSkillIfFacilitator()
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
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'addedBy' => $user
            ])
        );

        $response = $this->postJson('/api/skill', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
            'skill_type_id' => $skillType->id
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'skill' => [
                    'name' => 'PHP',
                    'description' => 'for creating backend solutions for websites',
                ]
            ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);

        $this->assertDatabaseMissing('activities', [
            'performedon_type' => 'App\Models\Skill',
            'performedon_id' => 1,
            'action' => 'create',
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $facilitator->id,
        ]);
    }

    public function testCannotUpdateSkillIfNotAnAdminAndCreator()
    {
        $user = User::create([
            'username' => "mr_robertamoah",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah@yahoo.com",
        ]);

        $creator = User::create([
            'username' => "mr_robertamoah1",
            'first_name' => "Robert",
            'surname' => "Amoah",
            'password' => bcrypt("password"),
            'email' => "mr_robertamoah1@yahoo.com",
        ]);

        $userType = $creator->addedUserTypes()->create([
            'name' => UserType::ADMIN
        ]);

        $creator->userTypes()->attach($userType->id);

        $this->actingAs($user);
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'addedBy' => $creator
            ])
        );

        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for creating backend solutions for websites',
                'skillTypeId' => $skillType->id,
                'user' => $creator
            ])
        );
        
        $response = $this->postJson("/api/skill/{$skill->id}", [
            'name' => 'Programming Language',
            'description' => 'this is to help create web and native apps'
        ]);

        $response
            ->assertStatus(500);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);
    }
    
    public function testCannotUpdateSkillWithoutNameOrDescription()
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
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'addedBy' => $user
            ])
        );

        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for creating backend solutions for websites',
                'skillTypeId' => $skillType->id,
                'user' => $facilitator
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);

        $response = $this->postJson("/api/skill/{$skill->id}", []);

        $response
            ->assertStatus(500);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);
    }
    
    public function testCanUpdateSkillIfFacilitatorAndCreator()
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
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'addedBy' => $user
            ])
        );

        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for creating backend solutions for websites',
                'skillTypeId' => $skillType->id,
                'user' => $facilitator
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);

        $response = $this->postJson("/api/skill/{$skill->id}", [
            'name' => 'PHP8',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'skill' => [
                    'name' => 'PHP8',
                    'description' => 'for creating backend solutions for websites',
                ]
            ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP8',
            'description' => 'for creating backend solutions for websites',
        ]);

        $this->assertDatabaseMissing('activities', [
            'performedon_type' => 'App\Models\Skill',
            'performedon_id' => 1,
            'action' => 'update',
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $facilitator->id,
        ]);
    }
    
    public function testCanUpdateSkillIfAdminAndNotCreator()
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

        $this->actingAs($user);
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'addedBy' => $facilitator
            ])
        );

        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for creating backend solutions for websites',
                'skillTypeId' => $skillType->id,
                'user' => $facilitator
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);

        $response = $this->postJson("/api/skill/{$skill->id}", [
            'name' => 'PHP8',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'skill' => [
                    'name' => 'PHP8',
                    'description' => 'for creating backend solutions for websites',
                ]
            ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP8',
            'description' => 'for creating backend solutions for websites',
        ]);

        $this->assertDatabaseHas('activities', [
            'performedon_type' => 'App\Models\Skill',
            'performedon_id' => 1,
            'action' => 'update',
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
        ]);
    }

    public function testCannotDeleteSkillIfNotAnAdminAndCreator()
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
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'addedBy' => $user
            ])
        );

        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for creating backend solutions for websites',
                'skillTypeId' => $skillType->id,
                'user' => $user
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);
        
        $response = $this->deleteJson("/api/skill/{$skill->id}");

        $response
            ->assertStatus(500);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);
    }

    public function testCannotDeleteSkillWithoutAValidSkillId()
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
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'addedBy' => $user
            ])
        );

        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for creating backend solutions for websites',
                'skillTypeId' => $skillType->id,
                'user' => $user
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);
        
        $response = $this->deleteJson("/api/skill/2");

        $response
            ->assertStatus(500);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);
    }

    public function testCanDeleteSkillIfAnAdmin()
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

        $this->actingAs($user);
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'addedBy' => $facilitator
            ])
        );

        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for creating backend solutions for websites',
                'skillTypeId' => $skillType->id,
                'user' => $facilitator
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);
        
        $response = $this->deleteJson("/api/skill/{$skill->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);

        $this->assertDatabaseMissing('skills', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);

        $this->assertDatabaseHas('activities', [
            'performedon_type' => 'App\Models\Skill',
            'performedon_id' => $skill->id,
            'action' => 'delete',
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
        ]);
    }

    public function testCanDeleteSkillIfAFacilitatorAndCreator()
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
        
        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Programming Language',
                'addedBy' => $facilitator
            ])
        );

        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'for creating backend solutions for websites',
                'skillTypeId' => $skillType->id,
                'user' => $facilitator
            ])
        );

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);
        
        $response = $this->deleteJson("/api/skill/{$skill->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);

        $this->assertDatabaseMissing('skills', [
            'name' => 'PHP',
            'description' => 'for creating backend solutions for websites',
        ]);

        $this->assertDatabaseMissing('activities', [
            'performedon_type' => 'App\Models\Skill',
            'performedon_id' => $skill->id,
            'action' => 'delete',
            'performedby_type' => 'App\Models\User',
            'performedby_id' => $user->id,
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

        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'this is used to create backend solutions for websites',
                'user' => $admin,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'this is used to create backend solutions for websites',
        ]);

        $this->actingAs($user);

        $response = $this->get("/api/skill/?id={$skill->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'skill' => [
                    'name' => 'PHP',
                    'description' => 'this is used to create backend solutions for websites',
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

        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'this is used to create backend solutions for websites',
                'user' => $admin,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
            'description' => 'this is used to create backend solutions for websites',
        ]);

        $this->actingAs($user);

        $response = $this->get("/api/skill?name={$skill->name}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'skill' => [
                    'name' => 'PHP',
                    'description' => 'this is used to create backend solutions for websites',
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

        (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP',
                'description' => 'this is used to create backend solutions for websites',
                'user' => $admin,
                'skillTypeId' => $skillType->id
            ])
        );

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => 'Designing Language',
                'addedBy' => $admin
            ])
        );

        (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => 'PHP8',
                'description' => 'this is used to create backend solutions for websites',
                'user' => $admin,
                'skillTypeId' => $skillType->id
            ])
        );

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Programming Language',
        ]);

        $this->assertDatabaseHas('skill_types', [
            'name' => 'Designing Language',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP',
        ]);

        $this->assertDatabaseHas('skills', [
            'name' => 'PHP8',
        ]);

        $this->actingAs($user);

        $response = $this->get("/api/skills?name=php");

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['name' => 'PHP'],
                    ['name' => 'PHP8'],
                ]
            ]);
    }
}
