<?php

namespace Tests\Unit;

use App\DTOs\AuthorizationDTO;
use App\Enums\PermissionEnum;
use App\Enums\RelationshipTypeEnum;
use App\Exceptions\AuthorizationException;
use App\Exceptions\ServiceException;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserType;
use App\Services\AuthorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase,
    WithFaker;

    public function testCannotSyncAuthoriztionAndUserWithoutUser()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
 
        AuthorizationService::new()->attachAuthorizationsAndUsers(
            AuthorizationDTO::new()
        );
    }

    public function testCannotSyncAuthoriztionAndUserWithoutAuthorizable()
    {
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage("Sorry! A Company/Project must be given to perform this action.");
 
        $user = User::factory()
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        AuthorizationService::new()->attachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $user
            ])
        );
    }

    public function testCannotSyncAuthoriztionAndUserWithoutAuthorized()
    {
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage("Sorry! The User to be authorized must be given to perform this action.");
 
        $user = User::factory()
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);

        AuthorizationService::new()->attachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $user,
                "authorizable" => $authorizable,
            ])
        );
    }

    public function testCannotSyncAuthoriztionAndUserWithoutAuthorization()
    {
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage("Sorry! A Role/Permission must be given to perform this action.");
 
        [$user, $authorized] = User::factory()->count(2)
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);

        AuthorizationService::new()->attachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $user,
                "authorizable" => $authorizable,
                "authorized" => $authorized,
            ])
        );
    }

    public function testCannotSyncAuthoriztionAndUserWhenNotAdminOrOwnerAndAuthorizationIsRole()
    {
        $this->expectException(AuthorizationException::class);
 
        [$user, $authorized, $other] = User::factory()->count(3)
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);

        $authorization = Role::factory()->create([
            "user_id" => 10
        ]);

        $this->expectExceptionMessage("Sorry! You are not authorized to perform this action on {$authorizable->name} Company.");

        AuthorizationService::new()->attachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $other,
                "authorizable" => $authorizable,
                "authorized" => $authorized,
                "authorization" => $authorization,
            ])
        );
    }

    public function testCanSyncAuthoriztionAndUserWhenAnAdminAndAuthorizedIsNotParticipant()
    {
        [$user, $authorized] = User::factory()->count(2)
            ->hasAttached(UserType::factory([
                "name" => UserType::ADMIN
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);

        $authorization = Role::factory()->create([
            "user_id" => 10
        ]);

        AuthorizationService::new()->attachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $user,
                "authorizable" => $authorizable,
                "authorized" => $authorized,
                "authorization" => $authorization,
            ])
        );

        $this->assertDatabaseHas("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);
    }

    public function testCanSyncAuthoriztionAndUserWhenOwnerAndAuthorizedIsNotParticipant()
    {
        $this->expectException(AuthorizationException::class);

        [$user, $authorized] = User::factory()->count(2)
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);

        $authorization = Role::factory()->create([
            "user_id" => $user->id
        ]);

        $classBasename = class_basename($authorizable);
        $this->expectExceptionMessage("Sorry! {$authorized->name} is not participating in the {$classBasename} with name {$authorizable->name}.");

        AuthorizationService::new()->attachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $user,
                "authorizable" => $authorizable,
                "authorized" => $authorized,
                "authorization" => $authorization,
            ])
        );

        $this->assertDatabaseMissing("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);
    }

    public function testCanSyncAuthoriztionAndUserWhenAnAdminAndNotOwnerOfAutorization()
    {
        [$user, $authorized] = User::factory()->count(2)
            ->hasAttached(UserType::factory([
                "name" => UserType::ADMIN
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);

        $authorization = Role::factory()->create([
            "user_id" => 10
        ]);

        AuthorizationService::new()->attachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $user,
                "authorizable" => $authorizable,
                "authorized" => $authorized,
                "authorization" => $authorization,
            ])
        );

        $this->assertDatabaseHas("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);
    }

    public function testCannotSyncAuthoriztionAndUserWhenAuthorizableOwnerAndNotOwnerOfPrivateRole()
    {
        $this->expectException(AuthorizationException::class);

        [$user, $authorized, $other] = User::factory()->count(3)
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);
        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $authorization = Role::factory()->create([
            "user_id" => $other->id,
            "public" => 0
        ]);

        $this->expectExceptionMessage("Sorry! You are not authorized to perform this action on {$authorization->name} role.");

        AuthorizationService::new()->attachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $user,
                "authorizable" => $authorizable,
                "authorized" => $authorized,
                "authorization" => $authorization,
            ])
        );

        $this->assertDatabaseMissing("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);
    }

    public function testCanSyncAuthoriztionAndUserWhenAuthorizableOwnerAndOwnerOfRole()
    {
        [$user, $authorized] = User::factory()->count(2)
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);
        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $authorization = Role::factory()->create([
            "user_id" => $user->id
        ]);

        AuthorizationService::new()->attachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $user,
                "authorizable" => $authorizable,
                "authorized" => $authorized,
                "authorization" => $authorization,
            ])
        );

        $this->assertDatabaseHas("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);
    }

    public function testCannotDetachAuthoriztionFromUserWithoutUser()
    {
        [$user, $authorized] = User::factory()->count(2)
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);
        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $authorization = Role::factory()->create([
            "user_id" => $user->id
        ]);

        AuthorizationService::new()->attachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $user,
                "authorizable" => $authorizable,
                "authorized" => $authorized,
                "authorization" => $authorization,
            ])
        );

        $this->assertDatabaseHas("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);

        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');

        AuthorizationService::new()->detachAuthorizationsAndUsers(
            AuthorizationDTO::new()
        );
    }

    public function testCannotDetachAuthoriztionFromUserWithoutAuthorizationId()
    {
        [$user, $authorized] = User::factory()->count(2)
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);
        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $authorization = Role::factory()->create([
            "user_id" => $user->id
        ]);

        AuthorizationService::new()->attachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $user,
                "authorizable" => $authorizable,
                "authorized" => $authorized,
                "authorization" => $authorization,
            ])
        );

        $this->assertDatabaseHas("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Sorry! A valid authorization is required to perform this action.');

        AuthorizationService::new()->detachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $user
            ])
        );
    }

    public function testCannotDetachAuthoriztionFromUserWhenNotAuthorized()
    {
        [$user, $authorized, $other] = User::factory()->count(3)
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);
        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $authorization = Role::factory()->create([
            "user_id" => $user->id
        ]);

        $auth = AuthorizationService::new()->attachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $user,
                "authorizable" => $authorizable,
                "authorized" => $authorized,
                "authorization" => $authorization,
            ])
        );

        $this->assertDatabaseHas("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage("Sorry! You are not authorized to remove authorization with name {$authorization->name} from {$authorized->name}.");

        AuthorizationService::new()->detachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $other,
                "mainAuthorizationId" => $auth->id
            ])
        );
    }

    public function testCanDetachAuthoriztionFromUserWhenAdmin()
    {
        [$user, $authorized] = User::factory()->count(3)
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        $admin = User::factory()
            ->hasAttached(UserType::factory([
                "name" => UserType::ADMIN
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);
        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $authorization = Role::factory()->create([
            "user_id" => $user->id
        ]);

        $auth = AuthorizationService::new()->attachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $user,
                "authorizable" => $authorizable,
                "authorized" => $authorized,
                "authorization" => $authorization,
            ])
        );

        $this->assertDatabaseHas("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);

        AuthorizationService::new()->detachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $admin,
                "mainAuthorizationId" => $auth->id
            ])
        );

        $this->assertDatabaseMissing("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);
    }

    public function testCanDetachAuthoriztionFromUserWhenCreatedAuthorization()
    {
        [$user, $authorized, $other] = User::factory()->count(3)
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);
        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $authorization = Role::factory()->create([
            "user_id" => $user->id
        ]);

        $auth = $other->authorizations()->create();
        $auth->authorizable()->associate($authorizable);
        $auth->authorization()->associate($authorization);
        $auth->authorized()->associate($authorized);
        $auth->save();

        $this->assertDatabaseHas("authorizations", [
            "user_id" => $other->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);

        AuthorizationService::new()->detachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $other,
                "mainAuthorizationId" => $auth->id
            ])
        );

        $this->assertDatabaseMissing("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);
    }

    public function testCanDetachAuthoriztionFromUserWhenAuthorized()
    {
        [$user, $authorized, $other] = User::factory()->count(3)
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);
        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $authorization = Role::factory()->create([
            "user_id" => $user->id
        ]);

        $auth = $other->authorizations()->create();
        $auth->authorizable()->associate($authorizable);
        $auth->authorization()->associate($authorization);
        $auth->authorized()->associate($authorized);
        $auth->save();

        $this->assertDatabaseHas("authorizations", [
            "user_id" => $other->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);

        AuthorizationService::new()->detachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $authorized,
                "mainAuthorizationId" => $auth->id
            ])
        );

        $this->assertDatabaseMissing("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);
    }

    public function testCanDetachAuthoriztionFromUserWhenOwnerOfAuthorizable()
    {
        [$user, $authorized, $other] = User::factory()->count(3)
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);
        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $authorization = Role::factory()->create([
            "user_id" => $user->id
        ]);

        $auth = $other->authorizations()->create();
        $auth->authorizable()->associate($authorizable);
        $auth->authorization()->associate($authorization);
        $auth->authorized()->associate($authorized);
        $auth->save();

        $this->assertDatabaseHas("authorizations", [
            "user_id" => $other->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);

        AuthorizationService::new()->detachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $user,
                "mainAuthorizationId" => $auth->id
            ])
        );

        $this->assertDatabaseMissing("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);
    }

    public function testCanDetachPermissionFromUserWhenAuthorizedToRemoveAuthorizations()
    {
        [$user, $authorized, $other] = User::factory()->count(3)
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);
        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $authorization = Permission::factory()->create([
            "user_id" => $user->id,
            "name" => PermissionEnum::REMOVEAUTHORIZATIONS->value
        ]);

        $auth = $user->authorizations()->create();
        $auth->authorizable()->associate($authorizable);
        $auth->authorization()->associate($authorization);
        $auth->authorized()->associate($other);
        $auth->save();

        $authorization = Role::factory()->create([
            "user_id" => $user->id
        ]);

        $auth = $user->authorizations()->create();
        $auth->authorizable()->associate($authorizable);
        $auth->authorization()->associate($authorization);
        $auth->authorized()->associate($authorized);
        $auth->save();

        $this->assertDatabaseHas("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);

        AuthorizationService::new()->detachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $other,
                "mainAuthorizationId" => $auth->id
            ])
        );

        $this->assertDatabaseMissing("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);
    }

    public function testCanDetachRoleFromUserWhenAuthorizedToRemoveAuthorizations()
    {
        [$user, $authorized, $other] = User::factory()->count(3)
            ->hasAttached(UserType::factory([
                "name" => UserType::FACILITATOR
            ]), [], "userTypes")
            ->create();

        $authorizable = Company::factory()->create([
            "user_id" => $user->id
        ]);
        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyMember->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $relation = $authorizable->addedByRelations()->create([
            "relationship_type" => RelationshipTypeEnum::companyAdministrator->value
        ]);
        $relation->to()->associate($authorized);
        $relation->save();

        $permission = Permission::factory()->create([
            "user_id" => $user->id,
            "name" => PermissionEnum::REMOVEAUTHORIZATIONS->value
        ]);

        $role = Role::factory()->create([
            "user_id" => $user->id
        ]);
        $role->permissions()->attach($permission);

        $auth = $user->authorizations()->create();
        $auth->authorizable()->associate($authorizable);
        $auth->authorization()->associate($role);
        $auth->authorized()->associate($other);
        $auth->save();

        $authorization = Role::factory()->create([
            "user_id" => $user->id
        ]);

        $auth = $user->authorizations()->create();
        $auth->authorizable()->associate($authorizable);
        $auth->authorization()->associate($authorization);
        $auth->authorized()->associate($authorized);
        $auth->save();

        $this->assertDatabaseHas("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);

        AuthorizationService::new()->detachAuthorizationsAndUsers(
            AuthorizationDTO::new()->fromArray([
                "user" => $other,
                "mainAuthorizationId" => $auth->id
            ])
        );

        $this->assertDatabaseMissing("authorizations", [
            "user_id" => $user->id,
            "authorizable_type" => $authorizable::class,
            "authorizable_id" => $authorizable->id,
            "authorized_type" => $authorized::class,
            "authorized_id" => $authorized->id,
            "authorization_type" => $authorization::class,
            "authorization_id" => $authorization->id,
        ]);
    }
}
