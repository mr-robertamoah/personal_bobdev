<?php

namespace Tests\Unit;

use App\Actions\UserTypes\RemoveUserTypeAction;
use App\Actions\UserTypes\BecomeUserTypeAction;
use App\Actions\UserTypes\CanAttachOrDetachUserTypeAction;
use App\DTOs\UserTypeDTO;
use App\Exceptions\UserTypeException;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserTypeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('users')->delete();
        DB::table('user_types')->delete();
    }
    
    public function testCanAttachOrDetachUserTypeActionFailsWhenNotAuthorized()
    {

        $this->assertFalse(
            (new CanAttachOrDetachUserTypeAction)->execute(
                User::factory()->make(['id' => 1]),
                User::factory()->make(['id' => 2])
            )
        );
    }

    public function testCanAttachOrDetachUserTypeActionSucceedsWhenAdmin()
    {

        $this->assertTrue(
            (new CanAttachOrDetachUserTypeAction)->execute(
                User::factory()->has(UserType::factory()->state(['name' => UserType::ADMIN]))->make(['id' => 1]),
                User::factory()->make(['id' => 1])
            )
        );
    }

    public function testCanAttachOrDetachUserTypeActionSucceedsAuthUser()
    {
        $user = User::factory()->make([
            'id' => 1
        ]);
        
        $this->assertTrue(
            (new CanAttachOrDetachUserTypeAction)->execute(
                $user,
                $user
            )
        );
    }

    public function testBecomeUserTypeActionSucceedsWhenSuperAdminAddsAdmin()
    {
        $this->createUserTypes();
        
        $adminUser = User::factory()->create();
        $adminUser->userTypes()->attach(UserType::where('name', UserType::SUPERADMIN)->first()->id);

        $userType = (new BecomeUserTypeAction)->execute(
            UserTypeDTO::fromArray(['name' => UserType::ADMIN])
                ->withUser($adminUser)
                ->withAttachedUser(User::factory()->create())
        );

        $this->assertEquals(UserType::ADMIN, $userType->name);
    }

    public function testBecomeUserTypeActionFailsWhenNotSuperAdminTriesToAddAdmin()
    {
        $this->expectException(UserTypeException::class);
        $this->expectExceptionMessage("Sorry! Only a Super Administrator is allowed to perform this action.");

        $this->createUserTypes();
        
        $adminUser = User::factory()->create();
        $adminUser->userTypes()->attach(UserType::where('name', UserType::ADMIN)->first()->id);

        $userType = (new BecomeUserTypeAction)->execute(
            UserTypeDTO::fromArray(['name' => UserType::ADMIN])
                ->withUser($adminUser)
                ->withAttachedUser(User::factory()->create())
        );
        
    }

    public function testBecomeUserTypeActionSucceedsWhenAuthorizedAsAdmin()
    {
        $this->createUserTypes();
        
        $adminUser = User::factory()->create();
        $adminUser->userTypes()->attach(UserType::where('name', UserType::ADMIN)->first()->id);

        $userType = (new BecomeUserTypeAction)->execute(
            UserTypeDTO::fromArray(['name' => UserType::STUDENT])
                ->withUser($adminUser)
                ->withAttachedUser(User::factory()->create())
        );

        $this->assertEquals(UserType::STUDENT, $userType->name);
    }

    public function testBecomeUserTypeActionSucceedsWhenAuthorizedAsCurrentUser()
    {
        $this->createUserTypes();

        $currentUser = User::factory()->create();

        $userType = (new BecomeUserTypeAction)->execute(
            UserTypeDTO::fromArray(['name' => UserType::STUDENT])
                ->withUser($currentUser)
                ->withAttachedUser($currentUser)
        );

        $this->assertEquals(UserType::STUDENT, $userType->name);
    }

    public function testBecomeUserTypeActionFailsWhenNotAuthorized()
    {
        $this->createUserTypes();
        
        $this->expectException(UserTypeException::class);

        (new BecomeUserTypeAction)->execute(
            UserTypeDTO::fromArray(['name' => UserType::STUDENT])
                ->withUser(User::factory()->create())
                ->withAttachedUser(User::factory()->create())
        );

    }

    public function testBecomeUserTypeActionFailsWhenWrongUserTypeNameIsGiven()
    {
        $this->createUserTypes();
        
        $this->expectException(UserTypeException::class);
        $this->expectExceptionMessage("Sorry! There is no user type with the name hey.");

        $currentUser = User::factory()->create();

        (new BecomeUserTypeAction)->execute(
            UserTypeDTO::fromArray(['name' => 'hey'])
                ->withUser($currentUser)
                ->withAttachedUser($currentUser)
        );

    }

    public function testBecomeUserTypeActionFailsWhenUserAlreadyHasType()
    {
        $this->createUserTypes();

        $this->expectException(UserTypeException::class);
        $this->expectExceptionMessage("Sorry! User is already of type with name student.");
        
        $currentUser = User::factory()->create();
        $currentUser->userTypes()->attach(UserType::where('name', UserType::STUDENT)->first()->id);

        (new BecomeUserTypeAction)->execute(
            UserTypeDTO::fromArray(['name' => 'student'])
                ->withUser($currentUser)
                ->withAttachedUser($currentUser)
        );

    }

    public function testRemoveUserTypeActionSucceedsWhenSuperAdminRemovesAdmin()
    {
        $this->createUserTypes();
        
        $superAdminUser = User::factory()->create();
        $superAdminUser->userTypes()->attach(UserType::where('name', UserType::SUPERADMIN)->first()->id);
        
        $adminUser = User::factory()->create();
        $adminUser->userTypes()->attach(UserType::where('name', UserType::ADMIN)->first()->id);

        $result = (new RemoveUserTypeAction)->execute(
            UserTypeDTO::fromArray(['name' => UserType::ADMIN])
                ->withUser($superAdminUser)
                ->withAttachedUser($adminUser)
        );

        $this->assertTrue(boolval($result));
    }

    public function testRemoveUserTypeActionFailsWhenNotSuperAdminTriesToAddAdmin()
    {
        $this->expectException(UserTypeException::class);
        $this->expectExceptionMessage("Sorry! Only a Super Administrator is allowed to perform this action.");

        $this->createUserTypes();
        
        $adminUser = User::factory()->create();
        $adminUser->userTypes()->attach(UserType::where('name', UserType::ADMIN)->first()->id);

        (new RemoveUserTypeAction)->execute(
            UserTypeDTO::fromArray(['name' => UserType::ADMIN])
                ->withUser($adminUser)
                ->withAttachedUser($adminUser)
        );
        
    }

    public function testRemoveUserTypeActionSucceedsWhenAuthorizedAsAdmin()
    {
        $this->createUserTypes();
        
        $adminUser = User::factory()->create();
        $adminUser->userTypes()->attach(UserType::where('name', UserType::ADMIN)->first()->id);

        $userType = UserType::where('name', UserType::STUDENT)->first();
        
        $attachedUser = User::factory()->create();
        $attachedUser->userTypes()->attach($userType->id);
        

        $result = (new RemoveUserTypeAction)->execute(
            UserTypeDTO::fromArray(['name' => UserType::STUDENT])
                ->withUser($adminUser)
                ->withAttachedUser($attachedUser)
        );

        $this->assertTrue(boolval($result));
    }

    public function testRemoveUserTypeActionSucceedsWhenAuthorizedAsCurrentUser()
    {
        $this->createUserTypes();

        $userType = UserType::where('name', UserType::STUDENT)->first();
        
        $attachedUser = User::factory()->create();
        $attachedUser->userTypes()->attach($userType->id);

        $result = (new RemoveUserTypeAction)->execute(
            UserTypeDTO::fromArray(['name' => UserType::STUDENT])
                ->withUser($attachedUser)
                ->withAttachedUser($attachedUser)
        );

        $this->assertTrue( boolval($result));
    }

    public function testRemoveUserTypeActionFailsWhenNotAuthorized()
    {
        $this->createUserTypes();
        
        $this->expectException(UserTypeException::class);

        (new RemoveUserTypeAction)->execute(
            UserTypeDTO::fromArray(['name' => UserType::STUDENT])
                ->withUser(User::factory()->create())
                ->withAttachedUser(User::factory()->create())
        );

    }

    public function testRemoveUserTypeActionFailsWhenWrongUserTypeNameIsGiven()
    {
        $this->createUserTypes();
        
        $this->expectException(UserTypeException::class);
        $this->expectExceptionMessage("Sorry! There is no user type with the name hey.");

        $currentUser = User::factory()->create();

        $userType = UserType::where('name', UserType::STUDENT)->first();
        
        $currentUser->userTypes()->attach($userType->id);

        (new RemoveUserTypeAction)->execute(
            UserTypeDTO::fromArray(['name' => 'hey'])
                ->withUser($currentUser)
                ->withAttachedUser($currentUser)
        );

    }

    public function testRemoveUserTypeActionFailsWhenUserDoesNotHaveType()
    {
        $this->createUserTypes();

        $this->expectException(UserTypeException::class);
        $this->expectExceptionMessage("Sorry! User is not of student type.");
        
        $currentUser = User::factory()->create();

        (new RemoveUserTypeAction)->execute(
            UserTypeDTO::fromArray(['name' => 'student'])
                ->withUser($currentUser)
                ->withAttachedUser($currentUser)
        );

    }

    private function createUserTypes()
    {
        UserType::factory()
            ->count(6)
            ->state(new Sequence(
                ['name' => UserType::ADMIN],
                ['name' => UserType::SUPERADMIN],
                ['name' => UserType::STUDENT],
                ['name' => UserType::FACILITATOR],
                ['name' => UserType::DONOR],
            ))
            ->create(['user_id' => 1]);
    }
}
