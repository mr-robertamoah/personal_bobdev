<?php

namespace Tests\Unit;

use App\DTOs\RoleDTO;
use App\Exceptions\PermissionException;
use App\Exceptions\RoleException;
use App\Exceptions\ServiceException;
use App\Models\Authorization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserType;
use App\Services\RoleService;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase,
    WithFaker;

    public function testCannotCreateRoleWithoutUser()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
 
        RoleService::new()->createRole(
            RoleDTO::new()->fromArray([
                'name' => $this->faker->name()
            ])
        );
    }
    
    public function testCanCreateRoleWhenNotASuperAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
        
        $role = RoleService::new()->createRole(
            RoleDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user
            ])
        );

        $this->assertDatabaseHas("roles", [
            'user_id' => $user->id,
            'name' => $role->name
        ]);
    }
    
    public function testCannotCreateRoleWithoutName()
    {
        $this->expectException(RoleException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
            
        $this->expectExceptionMessage("Sorry! The name of the role is required.");
        
        RoleService::new()->createRole(
            RoleDTO::new()->fromArray([
                'user' => $user
            ])
        );
    }
    
    public function testCanCreateRoleWhenNameAlreadyExists()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $role = Role::factory()->create();

        RoleService::new()->createRole(
            RoleDTO::new()->fromArray([
                'name' => $role->name,
                'user' => $user
            ])
        );

        $this->assertDatabaseCount("roles", 2);
    }
    
    public function testCannotCreateRoleWithNonExistentClass()
    {
        $this->expectException(RoleException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
            
        $this->expectExceptionMessage("Sorry! The class of the objects to which the role applies, has to exist or be null.");
        
        RoleService::new()->createRole(
            RoleDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => "hey"
            ])
        );
    }
    
    public function testCannotCreateRoleWithUnauthorizedClass()
    {
        $this->expectException(RoleException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
            
        $this->expectExceptionMessage("Sorry! The class you provided is not allowed.");
        
        RoleService::new()->createRole(
            RoleDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => "user"
            ])
        );
    }
    
    public function testCanCreateRoleWhenSuperAdminAndWithValidClass()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $role = RoleService::new()->createRole(
            RoleDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => "company"
            ])
        );

        $this->assertDatabaseHas('roles', [
            'name' => $role->name,
            'class' => $role->class,
            'id' => $role->id,
            'user_id' => $user->id,
        ]);
    }
    
    public function testCanCreateRoleWhenSuperAdminAndNullableClass()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $role = RoleService::new()->createRole(
            RoleDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => null
            ])
        );

        $this->assertDatabaseHas('roles', [
            'name' => $role->name,
            'class' => $role->class,
            'id' => $role->id,
            'user_id' => $user->id,
        ]);
    }

    public function testCannotUpdateRoleWithoutUser()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
 
        $role = Role::factory()->create();

        RoleService::new()->updateRole(
            RoleDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'roleId' => $role->id
            ])
        );
    }
    
    public function testCanUpdateRoleWhenOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $role = Role::factory()->create([
            "user_id" => $user->id
        ]);

        $updatedRole = RoleService::new()->updateRole(
            RoleDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'roleId' => $role->id
            ])
        );

        $this->assertNotEquals($updatedRole->name, $role->name);

        $this->assertDatabaseHas("roles", [
            'name' => $updatedRole->name
        ]);
    }
    
    public function testCannotUpdateRoleWithoutRoleId()
    {
        $this->expectException(RoleException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $this->expectExceptionMessage("Sorry! A valid role is required for this action.");
        
        RoleService::new()->updateRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                "name" => $this->faker->name()
            ])
        );
    }
    
    public function testCannotUpdateRoleWithoutAnyData()
    {
        $this->expectException(RoleException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $role = Role::factory()->create();
        
        $this->expectExceptionMessage("Sorry! You need to provide at least a name, class or description in order to update this role.");
        
        RoleService::new()->updateRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                "roleId" => $role->id
            ])
        );
    }
    
    public function testCanUpdateRoleWhenNameAlreadyExists()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $role = Role::factory()->create();
        $otherRole = Role::factory()->create();

        $otherRole = RoleService::new()->updateRole(
            RoleDTO::new()->fromArray([
                'name' => $role->name,
                'user' => $user,
                'roleId' => $otherRole->id
            ])
        );

        $this->assertEquals($otherRole->name, $role->name);
    }
    
    public function testCannotUpdateRoleWithNonExistentClass()
    {
        $this->expectException(RoleException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $role = Role::factory()->create();
          
        $this->expectExceptionMessage("Sorry! The class of the objects to which the role applies, has to exist or be null.");
        
        RoleService::new()->updateRole(
            RoleDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => "hey",
                'roleId' => $role->id
            ])
        );
    }
    
    public function testCannotUpdateRoleWithUnauthorizedClass()
    {
        $this->expectException(RoleException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $role = Role::factory()->create();
            
        $this->expectExceptionMessage("Sorry! The class you provided is not allowed.");
        
        RoleService::new()->updateRole(
            RoleDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => "user",
                'roleId' => $role->id
            ])
        );
    }
    
    public function testCanUpdateRoleWhenSuperAdminAndWithValidClass()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $role = Role::factory()->create();
        
        $role = RoleService::new()->updateRole(
            RoleDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => "company",
                'roleId' => $role->id
            ])
        );

        $this->assertDatabaseHas('roles', [
            'name' => $role->name,
            'class' => $role->class,
            'id' => $role->id,
        ]);
    }
    
    public function testCanUpdateRoleWhenSuperAdminAndNullableClass()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $role = Role::factory()->create([
            'class' => "App\\Models\\Company"
        ]);
        
        $role = RoleService::new()->updateRole(
            RoleDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => null,
                'roleId' => $role->id
            ])
        );

        $this->assertDatabaseHas('roles', [
            'name' => $role->name,
            'class' => null,
            'id' => $role->id,
            'user_id' => $user->id,
        ]);
    }
    
    public function testCanUpdateRoleWhenAdminAndWithValidClass()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
        
        $role = Role::factory()->create();
        
        $role = RoleService::new()->updateRole(
            RoleDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => "company",
                'roleId' => $role->id
            ])
        );

        $this->assertDatabaseHas('roles', [
            'name' => $role->name,
            'class' => $role->class,
            'id' => $role->id,
        ]);
    }
    
    public function testCanUpdateRoleWhenAdminAndNullableClass()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
        
        $role = Role::factory()->create();

        $this->assertDatabaseHas('roles', [
            'name' => $role->name,
            'class' => $role->class,
            'id' => $role->id,
        ]);
        
        $role = RoleService::new()->updateRole(
            RoleDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => null,
                'roleId' => $role->id
            ])
        );

        $this->assertDatabaseHas('roles', [
            'name' => $role->name,
            'class' => null,
            'id' => $role->id,
        ]);
    }

    public function testCannotDeleteRoleWithoutUser()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
 
        $role = Role::factory()->create();

        RoleService::new()->deleterole(
            RoleDTO::new()->fromArray([
                'roleId' => $role->id
            ])
        );
    }
    
    public function testCanDeleteRoleWhenOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $role = Role::factory()->create(["user_id" => $user->id]);

        RoleService::new()->deleteRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id
            ])
        );

        $this->assertDatabaseMissing("roles", [
            "id" => $role->id
        ]);
    }
    
    public function testCannotDeleteRoleWithoutRoleId()
    {
        $this->expectException(RoleException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $this->expectExceptionMessage("Sorry! A valid role is required for this action.");
        
        RoleService::new()->deleteRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
            ])
        );
    }
    
    public function testCanDeleteroleWhenSuperAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $role = Role::factory()->create([
            'class' => "App\\Models\\Company"
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => $role->name,
            'class' => $role->class,
            'id' => $role->id,
            'user_id' => $user->id,
        ]);
        
        RoleService::new()->deleteRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id
            ])
        );

        $this->assertDatabaseMissing('roles', [
            'name' => $role->name,
            'class' => $role->class,
            'id' => $role->id,
            'user_id' => $user->id,
        ]);
    }
    
    public function testCanDeleteroleAndRemoveAllAuthorizationsAndDetachFromRolesWhenSuperAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $other = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
        
        $permission = Permission::factory()->create([
            'class' => "App\\Models\\Company"
        ]);
        
        $role = Role::factory()->create([
            'class' => "App\\Models\\Company"
        ]);

        $authorization = Authorization::create([
            "user_id" => $user->id
        ]);
        $authorization->authorizable()->associate($other);
        $authorization->authorization()->associate($role);
        $authorization->save();


        $permission->roles()->attach($role);

        $this->assertDatabaseHas('roles', [
            'name' => $role->name,
            'class' => $role->class,
            'id' => $role->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('authorizations', [
            'authorization_type' => $role::class,
            'authorization_id' => $role->id,
            'authorizable_type' => $other::class,
            'authorizable_id' => $other->id,
        ]);

        $this->assertDatabaseHas('permission_role', [
            'permission_id' => $permission->id,
            'role_id' => $role->id,
        ]);
        
        RoleService::new()->deleteRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id
            ])
        );

        $this->assertDatabaseMissing('roles', [
            'name' => $role->name,
            'class' => $role->class,
            'id' => $role->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('authorizations', [
            'authorization_type' => $role::class,
            'authorization_id' => $role->id,
            'authorizable_type' => $other::class,
            'authorizable_id' => $other->id,
        ]);

        $this->assertDatabaseMissing('permission_role', [
            'permission_id' => $permission->id,
            'role_id' => $role->id,
        ]);
    }

    public function testCannotsyncPermissionsAndRoleWithoutUser()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
 
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()
        );
    }
    
    public function testCannotsyncPermissionsAndRoleWithoutRole()
    {
        $this->expectException(RoleException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $this->expectExceptionMessage("Sorry! A valid role is required for this action.");
        
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
            ])
        );
    }
    
    public function testCannotsyncPermissionsAndRoleWithoutPermissionIds()
    {
        $this->expectException(PermissionException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();

        $role = Role::factory()->create(["user_id" => $user->id]);
        
        $this->expectExceptionMessage("Sorry! You are required to provide ids of permissions.");
        
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id
            ])
        );
    }
    
    public function testCannotsyncPermissionsAndRoleWhenNotAdminOrOwnerOfRole()
    { 
        $this->expectException(RoleException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $role = Role::factory()->create(["user_id" => 10]);
        Permission::factory()->count(2)->create([
            'user_id' => $user->id,
            'class' => $role->class
        ]);
        
        $this->expectExceptionMessage("Sorry! You are not authorized to perform this action on {$role->name} role.");
        
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id,
                'permissionIds' => [1, 2]
            ])
        );
    }
    
    public function testCannotsyncPermissionsAndRoleWithEmptyPermissionIds()
    {
        $this->expectException(PermissionException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();

        $role = Role::factory()->create(["user_id" => $user->id]);
        
        $this->expectExceptionMessage("Sorry! You are required to provide ids of permissions.");
        
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id,
                'permissionIds' => []
            ])
        );
    }
    
    public function testCannotsyncPermissionsAndRoleWithPermissionIdsThatDoNotExist()
    {
        $this->expectException(PermissionException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();

        $role = Role::factory()->create(["user_id" => $user->id]);
        
        $this->expectExceptionMessage("Sorry! all permissions provided do not exist.");
        
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id,
                'permissionIds' => [1, 2, 3, 4]
            ])
        );
    }
    
    public function testCannotsyncPermissionsAndRoleWithSomePermissionIdsBeingInvalid()
    {
        $this->expectException(PermissionException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();

        $role = Role::factory()->create(["user_id" => $user->id]);
        Permission::factory()->count(2)->create([
            'user_id' => $user->id,
            'class' => $role->class
        ]);

        $this->expectExceptionMessage("Sorry! Permissions with [3, 4] ids are not valid.");
        
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id,
                'permissionIds' => [1, 2, 3, 4]
            ])
        );
    }
    
    public function testCannotsyncPermissionsAndRoleWithSomePermissionIdsThatHaveDifferentNonNullClasses()
    {
        $this->expectException(PermissionException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();

        $role = Role::factory()->create([
            "user_id" => $user->id,
            'class' => 'App\\Models\\Project'
        ]);
        Permission::factory()->count(4)
            ->state(new Sequence(
                ['class' => 'App\\Models\\Project'],
                ['class' => 'App\\Models\\Company'],
            ))->create([
            'user_id' => $user->id]);
        
        $this->expectExceptionMessage("Sorry! Permissions with [2, 4] ids do not have valid class to be attached to the role.");
        
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id,
                'permissionIds' => [1, 2, 3, 4]
            ])
        );
    }
    
    public function testCanSyncPermissionsAndRoleWithPermissionIdsHavingSameClassWhenAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();

        $role = Role::factory()->create(["user_id" => 10]);
        $permissions = Permission::factory()->count(2)->create([
            'user_id' => $user->id,
            'class' => $role->class
        ])->toArray();
        
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id,
                'permissionIds' => [1, 2]
            ])
        );

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[0]["id"]
        ]);

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[1]["id"]
        ]);
    }
    
    public function testCanSyncPermissionsAndRoleWithPermissionIdsHavingSameClassWhenOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $role = Role::factory()->create(["user_id" => $user->id]);
        $permissions = Permission::factory()->count(2)->create([
            'user_id' => $user->id,
            'class' => $role->class
        ])->toArray();
        
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id,
                'permissionIds' => [1, 2]
            ])
        );

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[0]["id"]
        ]);

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[1]["id"]
        ]);
    }
    
    public function testCanSyncPermissionsAndRoleThatHasANullClassWithPermissionIdsHavingAnyClassWhenAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();

        $role = Role::factory()->create([
            "user_id" => 10,
            'class' => null
        ]);
        $permissions = Permission::factory()->count(4)
            ->state(new Sequence(
                ['class' => 'App\\Models\\Project'],
                ['class' => 'App\\Models\\Company'],
                ['class' => null],
            ))->create([
            'user_id' => $user->id])->toArray();
        
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id,
                'permissionIds' => [1, 2, 3]
            ])
        );

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[0]["id"]
        ]);

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[1]["id"]
        ]);

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[2]["id"]
        ]);
    }
    
    public function testCanSyncPermissionsAndRoleThatHasANullClassWithPermissionIdsHavingAnyClassWhenOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $role = Role::factory()->create([
            "user_id" => $user->id,
            'class' => null
        ]);
        $permissions = Permission::factory()->count(4)
            ->state(new Sequence(
                ['class' => 'App\\Models\\Project'],
                ['class' => 'App\\Models\\Company'],
                ['class' => null],
            ))->create([
            'user_id' => $user->id])->toArray();
        
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id,
                'permissionIds' => [1, 2, 3]
            ])
        );

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[0]["id"]
        ]);

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[1]["id"]
        ]);

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[2]["id"]
        ]);
    }
    
    public function testCanSyncPermissionsAndRoleWithPermissionIdsHavingSameAndNullClassesWhenAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();

        $role = Role::factory()->create([
            "user_id" => 10,
            'class' => 'App\\Models\\Project'
        ]);
        $permissions = Permission::factory()->count(4)
            ->state(new Sequence(
                ['class' => 'App\\Models\\Project'],
                ['class' => null],
            ))->create([
            'user_id' => $user->id])->toArray();
        
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id,
                'permissionIds' => [1, 2, 3]
            ])
        );

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[0]["id"]
        ]);

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[1]["id"]
        ]);

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[2]["id"]
        ]);
    }
    
    public function testCanSyncPermissionsAndRoleWithPermissionIdsHavingSameAndNullClassesWhenOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $role = Role::factory()->create([
            "user_id" => $user->id,
            'class' => 'App\\Models\\Project'
        ]);
        $permissions = Permission::factory()->count(4)
            ->state(new Sequence(
                ['class' => 'App\\Models\\Project'],
                ['class' => null],
            ))->create([
            'user_id' => $user->id])->toArray();
        
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id,
                'permissionIds' => [1, 2, 3]
            ])
        );

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[0]["id"]
        ]);

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[1]["id"]
        ]);

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[2]["id"]
        ]);
    }
    
    public function testCanAttachAndDetachPermissionsAndRoleWithPermissionIdsHavingSameAndNullClassesWhenAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();

        $role = Role::factory()->create([
            "user_id" => 10,
            'class' => 'App\\Models\\Project'
        ]);
        $permissions = Permission::factory()->count(4)
            ->state(new Sequence(
                ['class' => 'App\\Models\\Project'],
                ['class' => null],
            ))->create([
            'user_id' => $user->id])->toArray();
            
        $role->permissions()->attach($permissions[3]['id']);

        $this->assertDatabaseHas("permission_role", [
            'permission_id' => $permissions[3]["id"],
            'role_id' => $role->id
        ]);
        
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id,
                'permissionIds' => [1, 2, 3, 4]
            ])
        );

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[0]["id"]
        ]);

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[1]["id"]
        ]);

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[2]["id"]
        ]);

        $this->assertDatabaseMissing("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[3]["id"]
        ]);
    }
    
    public function testCanAttachAndDetachPermissionsAndRoleWithPermissionIdsHavingSameAndNullClassesWhenOwner()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();

        $role = Role::factory()->create([
            "user_id" => $user->id,
            'class' => 'App\\Models\\Project'
        ]);
        $permissions = Permission::factory()->count(4)
            ->state(new Sequence(
                ['class' => 'App\\Models\\Project'],
                ['class' => null],
            ))->create([
            'user_id' => $user->id])->toArray();
            
        $role->permissions()->attach($permissions[3]['id']);

        $this->assertDatabaseHas("permission_role", [
            'permission_id' => $permissions[3]["id"],
            'role_id' => $role->id
        ]);
        
        RoleService::new()->syncPermissionsAndRole(
            RoleDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id,
                'permissionIds' => [1, 2, 3, 4]
            ])
        );

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[0]["id"]
        ]);

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[1]["id"]
        ]);

        $this->assertDatabaseHas("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[2]["id"]
        ]);

        $this->assertDatabaseMissing("permission_role", [
            'role_id' => $role->id,
            'permission_id' => $permissions[3]["id"]
        ]);
    }
}
