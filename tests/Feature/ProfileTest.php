<?php

namespace Tests\Feature;

use App\Enums\ProjectParticipantEnum;
use App\Enums\RelationshipTypeEnum;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use App\Traits\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase,
    WithFaker,
    TestTrait;
    
    public function testCannotGetProfileWithoutTypeParameterOfApiBeingUserOrCompany()
    {
        $user = $this->createUser();

        $response = $this->getJson("/api/profile/wrong-type/{$user->id}");
        
        $response->assertStatus(404)
            ->assertJson([
                "status" => false,
                "message" => "this is an unknown request."
            ]);
    }
    
    public function testCanGetProfileOfUserWithNoCompaniesOrProjectsAsGuest()
    {
        $user = $this->createUser();

        $response = $this->getJson("/api/profile/user/{$user->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "profile" => [
                    "user" => [
                        "id" => $user->id,
                        "firstName" => $user->first_name,
                    ],
                    'ownedCompanies' => [],
                    'memberingCompanies' => [],
                    'administeringCompanies' => [],
                    'facilitatorProjects' => [],
                    'learnerProjects' => [],
                    'sponsoredProjects' => [],
                    'parentProjects' => [],
                    'ownedProjects' => [],
                ]
            ]);
    }
    
    public function testCanGetProfileOfUserAndOwnedMemberingAndAdministeringCompaniesOfUserAsGuest()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        
        $ownedCompaniesCount = 2;
        $ownedCompanies = Company::factory()->count($ownedCompaniesCount)
            ->create([
                "user_id" => $user->id
            ]);
        
        $administeringCompaniesCount = 2;
        $administeringCompanies = Company::factory()->count($administeringCompaniesCount)
            ->create([
                "user_id" => $creator->id
            ]);
        for ($i=0; $i < $administeringCompaniesCount; $i++) { 
            $relation = $administeringCompanies[$i]->addedByRelations()
                ->create([
                    "relationship_type" => RelationshipTypeEnum::companyAdministrator->value
                ]);
            $relation->to()->associate($user);
            $relation->save();
        }
        
        $memberingCompaniesCount = 2;
        $memberingCompanies = Company::factory()->count($memberingCompaniesCount)
            ->create([
                "user_id" => $creator->id
            ]);
        for ($i=0; $i < $memberingCompaniesCount; $i++) { 
            $relation = $memberingCompanies[$i]->addedByRelations()
                ->create([
                    "relationship_type" => RelationshipTypeEnum::companyMember->value
                ]);
            $relation->to()->associate($user);
            $relation->save();
        }

        $response = $this->getJson("/api/profile/user/{$user->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "profile" => [
                    "user" => [
                        "id" => $user->id,
                        "firstName" => $user->first_name,
                    ],
                    'ownedCompanies' => [
                        [
                            "id" => $ownedCompanies[0]->id,
                            "name" => $ownedCompanies[0]->name,
                        ],
                        [
                            "id" => $ownedCompanies[1]->id,
                            "name" => $ownedCompanies[1]->name,
                        ],
                    ],
                    'memberingCompanies' => [
                        [
                            "id" => $memberingCompanies[0]->id,
                            "name" => $memberingCompanies[0]->name,
                        ],
                        [
                            "id" => $memberingCompanies[1]->id,
                            "name" => $memberingCompanies[1]->name,
                        ],
                    ],
                    'administeringCompanies' => [
                        [
                            "id" => $administeringCompanies[0]->id,
                            "name" => $administeringCompanies[0]->name,
                        ],
                        [
                            "id" => $administeringCompanies[1]->id,
                            "name" => $administeringCompanies[1]->name,
                        ],
                    ],
                    'facilitatorProjects' => [],
                    'learnerProjects' => [],
                    'sponsoredProjects' => [],
                    'parentProjects' => [],
                    'ownedProjects' => [],
                ]
            ]);

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())
                ->profile->ownedCompanies
            ),
            $ownedCompaniesCount
        );

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())
                ->profile->memberingCompanies
            ),
            $memberingCompaniesCount
        );

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())
                ->profile->administeringCompanies
            ),
            $administeringCompaniesCount
        );
    }
    
    public function testCanGetProfileOfUserAndPersonalRelationsOfUserAsGuest()
    {
        $user = $this->createUser();
        $parent = $this->createUser();
        $ward = $this->createUser();

        $this->assertFalse($parent->isParent());
        $this->assertFalse($ward->isWard());

        $this->assertFalse($user->isParent());
        $this->assertFalse($user->isWard());
        
        $relation = $parent->addedByRelations()
            ->create([
                "relationship_type" => RelationshipTypeEnum::parent->value
            ]);
        $relation->to()->associate($user);
        $relation->save();
        
        $relation = $ward->addedByRelations()
            ->create([
                "relationship_type" => RelationshipTypeEnum::ward->value
            ]);
        $relation->to()->associate($user);
        $relation->save();

        $this->assertTrue($parent->isParent());
        $this->assertTrue($ward->isWard());

        $this->assertTrue($user->isParent());
        $this->assertTrue($user->isWard());

        $response = $this->getJson("/api/profile/user/{$user->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "profile" => [
                    "user" => [
                        "id" => $user->id,
                        "firstName" => $user->first_name,
                    ],
                    "wards" => [
                        [
                            "id" => $ward->id,
                            "username" => $ward->username,
                        ],
                    ],
                    "parents" => [
                        [
                            "id" => $parent->id,
                            "username" => $parent->username,
                        ],
                    ],
                    'ownedCompanies' => [],
                    'memberingCompanies' => [],
                    'administeringCompanies' => [],
                    'facilitatorProjects' => [],
                    'learnerProjects' => [],
                    'sponsoredProjects' => [],
                    'parentProjects' => [],
                    'ownedProjects' => [],
                ]
            ]);

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())
                ->profile->wards
            ),
            1
        );

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())
                ->profile->parents
            ),
            1
        );
    }
    
    public function testCanGetProfileOfUserOwnedParentSponsoredLearnerAndFacilitatorProjectsOfUserAsGuest()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $ward = $this->createUser();

        $this->assertFalse($ward->isWard());
        $this->assertFalse($user->isParent());
        
        $relation = $ward->addedByRelations()
            ->create([
                "relationship_type" => RelationshipTypeEnum::ward->value
            ]);
        $relation->to()->associate($user);
        $relation->save();

        $this->assertTrue($ward->isWard());
        $this->assertTrue($user->isParent());

        $company = Company::factory()->create([
            "user_id" => $creator->id
        ]);
        $relation = $company->addedByRelations()
            ->create([
                "relationship_type" => RelationshipTypeEnum::companyMember->value
            ]);
        $relation->to()->associate($user);
        $relation->save();
        
        $companyProjectsCount = 2;
        $companyProjects = Project::factory()->count($companyProjectsCount)
            ->create([
                "addedby_id" => $company->id,
                "addedby_type" => $company::class
            ]);
        
        $ownedProjectsCount = 2;
        $ownedProjects = Project::factory()->count($ownedProjectsCount)
            ->create([
                "addedby_id" => $user->id,
                "addedby_type" => $user::class
            ]);
        
        $facilitatorProjectsCount = 2;
        $facilitatorProjects = Project::factory()->count($facilitatorProjectsCount)
            ->create([
                "addedby_id" => $creator->id,
                "addedby_type" => $creator::class
            ]);
        for ($i=0; $i < $facilitatorProjectsCount; $i++) { 
            $participation = $facilitatorProjects[$i]->participants()
                ->create([
                    "participating_as" => ProjectParticipantEnum::facilitator->value
                ]);
            $participation->participant()->associate($user);
            $participation->save();
        }
        
        $learnerProjectsCount = 2;
        $learnerProjects = Project::factory()->count($learnerProjectsCount)
            ->create([
                "addedby_id" => $creator->id,
                "addedby_type" => $creator::class
            ]);
        for ($i=0; $i < $learnerProjectsCount; $i++) { 
            $participation = $learnerProjects[$i]->participants()
                ->create([
                    "participating_as" => ProjectParticipantEnum::learner->value
                ]);
            $participation->participant()->associate($user);
            $participation->save();
        }
        
        $sponsoredProjectsCount = 2;
        $sponsoredProjects = Project::factory()->count($sponsoredProjectsCount)
            ->create([
                "addedby_id" => $creator->id,
                "addedby_type" => $creator::class
            ]);
        for ($i=0; $i < $sponsoredProjectsCount; $i++) { 
            $participation = $sponsoredProjects[$i]->participants()
                ->create([
                    "participating_as" => ProjectParticipantEnum::sponsor->value
                ]);
            $participation->participant()->associate($user);
            $participation->save();
        }
        
        $parentProjectsCount = 2;
        $parentProjects = Project::factory()->count($parentProjectsCount)
            ->create([
                "addedby_id" => $creator->id,
                "addedby_type" => $creator::class
            ]);
        for ($i=0; $i < $parentProjectsCount; $i++) { 
            $participation = $parentProjects[$i]->participants()
                ->create([
                    "participating_as" => ProjectParticipantEnum::learner->value
                ]);
            $participation->participant()->associate($ward);
            $participation->save();
        }

        $response = $this->getJson("/api/profile/user/{$user->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "profile" => [
                    "user" => [
                        "id" => $user->id,
                        "firstName" => $user->first_name,
                    ],
                    'ownedCompanies' => [],
                    'memberingCompanies' => [],
                    'administeringCompanies' => [],
                    'facilitatorProjects' => [
                        [
                            "id" => $facilitatorProjects[0]->id,
                            "name" => $facilitatorProjects[0]->name,
                        ],
                        [
                            "id" => $facilitatorProjects[1]->id,
                            "name" => $facilitatorProjects[1]->name,
                        ],
                    ],
                    'learnerProjects' => [
                        [
                            "id" => $learnerProjects[0]->id,
                            "name" => $learnerProjects[0]->name,
                        ],
                        [
                            "id" => $learnerProjects[1]->id,
                            "name" => $learnerProjects[1]->name,
                        ],
                    ],
                    'sponsoredProjects' => [
                        [
                            "id" => $sponsoredProjects[0]->id,
                            "name" => $sponsoredProjects[0]->name,
                        ],
                        [
                            "id" => $sponsoredProjects[1]->id,
                            "name" => $sponsoredProjects[1]->name,
                        ],
                    ],
                    'parentProjects' => [
                        [
                            "id" => $parentProjects[0]->id,
                            "name" => $parentProjects[0]->name,
                        ],
                        [
                            "id" => $parentProjects[1]->id,
                            "name" => $parentProjects[1]->name,
                        ],
                    ],
                    'ownedProjects' => [
                        [
                            "id" => $ownedProjects[0]->id,
                            "name" => $ownedProjects[0]->name,
                        ],
                        [
                            "id" => $ownedProjects[1]->id,
                            "name" => $ownedProjects[1]->name,
                        ],
                    ],
                    'companyProjects' => [
                        [
                            "id" => $companyProjects[0]->id,
                            "name" => $companyProjects[0]->name,
                        ],
                        [
                            "id" => $companyProjects[1]->id,
                            "name" => $companyProjects[1]->name,
                        ],
                    ],
                ]
            ]);

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())
                ->profile->ownedProjects
            ),
            $ownedProjectsCount
        );

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())
                ->profile->learnerProjects
            ),
            $learnerProjectsCount
        );

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())
                ->profile->facilitatorProjects
            ),
            $facilitatorProjectsCount
        );

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())
                ->profile->sponsoredProjects
            ),
            $sponsoredProjectsCount
        );

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())
                ->profile->parentProjects
            ),
            $parentProjectsCount
        );

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())
                ->profile->companyProjects
            ),
            $companyProjectsCount
        );
    }
    
    public function testCanGetProfileOfCompanyWithNoMembersOrProjectsAsGuest()
    {
        $user = $this->createUser();
        $company = Company::factory()->create(["user_id" => $user->id]);

        $response = $this->getJson("/api/profile/company/{$company->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "profile" => [
                    "company" => [
                        "id" => $company->id,
                        "name" => $company->name,
                    ],
                    'members' => [],
                    'officials' => [],
                    'sponsoredProjects' => [],
                    'ownedProjects' => [],
                ]
            ]);
    }
    
    public function testCanGetProfileOfCompanyWithMembersAndOfficialsOfCompanyAsGuest()
    {
        $user = $this->createUser();
        $membershipTypes = [
            RelationshipTypeEnum::companyAdministrator->value,
            RelationshipTypeEnum::companyMember->value,
        ];
        $members = [];
        $members[] = $this->createUser();
        $members[] = $this->createUser();
        $members[] = $this->createUser();
        $members[] = $this->createUser();
        $company = Company::factory()->create(["user_id" => $user->id]);
        
        $i = 0;
        $j = 2;
        foreach ($membershipTypes as $membershipType) {
            for (; $i < $j; $i++) { 
                $relation = $company->addedByRelations()->create([
                    "relationship_type" => $membershipType
                ]);
                $relation->to()->associate($members[$i]);
                $relation->save();
            }
            $j += 2;
        }
        $response = $this->getJson("/api/profile/company/{$company->id}");
        ds($response);
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "profile" => [
                    "company" => [
                        "id" => $company->id,
                        "name" => $company->name,
                    ],
                    'members' => [
                        [
                            "id" => $members[2]->id,
                            "name" => $members[2]->name,
                        ],
                        [
                            "id" => $members[3]->id,
                            "name" => $members[3]->name,
                        ],
                    ],
                    'officials' => [
                        [
                            "id" => $members[0]->id,
                            "name" => $members[0]->name,
                        ],
                        [
                            "id" => $members[1]->id,
                            "name" => $members[1]->name,
                        ],
                    ],
                    'sponsoredProjects' => [],
                    'ownedProjects' => [],
                ]
            ]);

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())
                ->profile->members
            ),
            2
        );

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())
                ->profile->officials
            ),
            2
        );
    }
    
    public function testCanGetProfileOfCompanyWithSponsoredAndOwnedOfCompanyAsGuest()
    {
        $user = $this->createUser();
        $creator = $this->createUser();
        $company = Company::factory()->create(["user_id" => $user->id]);
        
        $ownedProjectsCount = 2;
        $ownedProjects = Project::factory()->count($ownedProjectsCount)
            ->create([
                "addedby_id" => $company->id,
                "addedby_type" => $company::class
            ]);
        
        $sponsoredProjectsCount = 2;
        $sponsoredProjects = Project::factory()->count($sponsoredProjectsCount)
            ->create([
                "addedby_id" => $creator->id,
                "addedby_type" => $creator::class
            ]);
        for ($i=0; $i < $sponsoredProjectsCount; $i++) { 
            $participation = $sponsoredProjects[$i]->participants()
                ->create([
                    "participating_as" => ProjectParticipantEnum::sponsor->value
                ]);
            $participation->participant()->associate($company);
            $participation->save();
        }
        
        $response = $this->getJson("/api/profile/company/{$company->id}");
        ds($response);
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "profile" => [
                    "company" => [
                        "id" => $company->id,
                        "name" => $company->name,
                    ],
                    'members' => [],
                    'officials' => [],
                    'sponsoredProjects' => [
                        [
                            "id" => $sponsoredProjects[0]->id,
                            "name" => $sponsoredProjects[0]->name,
                        ],
                        [
                            "id" => $sponsoredProjects[1]->id,
                            "name" => $sponsoredProjects[1]->name,
                        ],
                    ],
                    'ownedProjects' => [
                        [
                            "id" => $ownedProjects[0]->id,
                            "name" => $ownedProjects[0]->name,
                        ],
                        [
                            "id" => $ownedProjects[1]->id,
                            "name" => $ownedProjects[1]->name,
                        ],
                    ],
                ]
            ]);

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())
                ->profile->ownedProjects
            ),
            $ownedProjectsCount
        );

        $this->assertEquals(
            count(json_decode($response->baseResponse->content())
                ->profile->sponsoredProjects
            ),
            $sponsoredProjectsCount
        );
    }
}
