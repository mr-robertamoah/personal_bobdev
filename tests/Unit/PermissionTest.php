<?php

namespace Tests\Unit;

use App\DTOs\PermissionDTO;
use App\Exceptions\PermissionException;
use App\Exceptions\RoleException;
use App\Exceptions\ServiceException;
use App\Models\Authorization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserType;
use App\Services\PermissionService;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase,
    WithFaker;

    public function testCannotCreatePermissionWithoutUser()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
 
        PermissionService::new()->createPermission(
            PermissionDTO::new()->fromArray([
                'name' => $this->faker->name()
            ])
        );
    }
    
    public function testCannotCreatePermissionWhenNotASuperAdmin()
    {
        $this->expectException(ServiceException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
            
        $this->expectExceptionMessage("Sorry! For this action, {$user->name} must be a super administrator.");
        
        PermissionService::new()->createPermission(
            PermissionDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user
            ])
        );
    }
    
    public function testCannotCreatePermissionWithoutName()
    {
        $this->expectException(PermissionException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
            
        $this->expectExceptionMessage("Sorry! The name of the permission is required.");
        
        PermissionService::new()->createPermission(
            PermissionDTO::new()->fromArray([
                'user' => $user
            ])
        );
    }
    
    public function testCannotCreatePermissionWhenNameAlreadyExists()
    {
        $this->expectException(PermissionException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $permission = Permission::factory()->create();
            
        $this->expectExceptionMessage("Sorry! The name '{$permission->name}' has already been taken.");

        PermissionService::new()->createPermission(
            PermissionDTO::new()->fromArray([
                'name' => $permission->name,
                'user' => $user
            ])
        );
    }
    
    public function testCannotCreatePermissionWithNonExistentClass()
    {
        $this->expectException(PermissionException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
            
        $this->expectExceptionMessage("Sorry! The class of the objects to which the permission applies, has to exist or be null.");
        
        PermissionService::new()->createPermission(
            PermissionDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => "hey"
            ])
        );
    }
    
    public function testCannotCreatePermissionWithUnauthorizedClass()
    {
        $this->expectException(PermissionException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
            
        $this->expectExceptionMessage("Sorry! The class you provided is not allowed.");
        
        PermissionService::new()->createPermission(
            PermissionDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => "user"
            ])
        );
    }
    
    public function testCanCreatePermissionWhenSuperAdminAndWithValidClass()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $permission = PermissionService::new()->createPermission(
            PermissionDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => "company"
            ])
        );

        $this->assertDatabaseHas('permissions', [
            'name' => $permission->name,
            'class' => $permission->class,
            'id' => $permission->id,
            'user_id' => $user->id,
        ]);
    }
    
    public function testCanCreatePermissionWhenSuperAdminAndNullableClass()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $permission = PermissionService::new()->createPermission(
            PermissionDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => null
            ])
        );

        $this->assertDatabaseHas('permissions', [
            'name' => $permission->name,
            'class' => $permission->class,
            'id' => $permission->id,
            'user_id' => $user->id,
        ]);
    }

    public function testCannotUpdatePermissionWithoutUser()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
 
        $permission = Permission::factory()->create();

        PermissionService::new()->updatePermission(
            PermissionDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'permissionId' => $permission->id
            ])
        );
    }
    
    public function testCannotUpdatePermissionWhenNotAnAdmin()
    {
        $this->expectException(ServiceException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::FACILITATOR
            ]), [], 'userTypes')
            ->create();
            
        $this->expectExceptionMessage("Sorry! For this action, {$user->name} must be a super administrator.");
        
        $permission = Permission::factory()->create();

        PermissionService::new()->updatePermission(
            PermissionDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'permissionId' => $permission->id
            ])
        );
    }
    
    public function testCannotUpdatePermissionWithoutPermissionId()
    {
        $this->expectException(PermissionException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $this->expectExceptionMessage("Sorry! A permission is required to perform this action.");
        
        PermissionService::new()->updatePermission(
            PermissionDTO::new()->fromArray([
                'user' => $user,
                "name" => $this->faker->name()
            ])
        );
    }
    
    public function testCannotUpdatePermissionWithoutAnyData()
    {
        $this->expectException(PermissionException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $permission = Permission::factory()->create();
        
        $this->expectExceptionMessage("Sorry! You need to provide at least a name, class or description in order to update this permission.");
        
        PermissionService::new()->updatePermission(
            PermissionDTO::new()->fromArray([
                'user' => $user,
                "permissionId" => $permission->id
            ])
        );
    }
    
    public function testCannotUpdatePermissionWhenNameAlreadyExists()
    {
        $this->expectException(PermissionException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $permission = Permission::factory()->create();
            
        $this->expectExceptionMessage("Sorry! The name '{$permission->name}' has already been taken.");

        PermissionService::new()->updatePermission(
            PermissionDTO::new()->fromArray([
                'name' => $permission->name,
                'user' => $user,
                'permissionId' => $permission->id
            ])
        );
    }
    
    public function testCannotUpdatePermissionWithNonExistentClass()
    {
        $this->expectException(PermissionException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $permission = Permission::factory()->create();
          
        $this->expectExceptionMessage("Sorry! The class of the objects to which the permission applies, has to exist or be null.");
        
        PermissionService::new()->updatePermission(
            PermissionDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => "hey",
                'permissionId' => $permission->id
            ])
        );
    }
    
    public function testCannotUpdatePermissionWithUnauthorizedClass()
    {
        $this->expectException(PermissionException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $permission = Permission::factory()->create();
            
        $this->expectExceptionMessage("Sorry! The class you provided is not allowed.");
        
        PermissionService::new()->updatePermission(
            PermissionDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => "user",
                'permissionId' => $permission->id
            ])
        );
    }
    
    public function testCanUpdatePermissionWhenSuperAdminAndWithValidClass()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $permission = Permission::factory()->create();
        
        $permission = PermissionService::new()->updatePermission(
            PermissionDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => "company",
                'permissionId' => $permission->id
            ])
        );

        $this->assertDatabaseHas('permissions', [
            'name' => $permission->name,
            'class' => $permission->class,
            'id' => $permission->id,
        ]);
    }
    
    public function testCanUpdatePermissionWhenSuperAdminAndNullableClass()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $permission = Permission::factory()->create([
            'class' => "App\\Models\\Company"
        ]);
        
        $permission = PermissionService::new()->updatePermission(
            PermissionDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => null,
                'permissionId' => $permission->id
            ])
        );

        $this->assertDatabaseHas('permissions', [
            'name' => $permission->name,
            'class' => null,
            'id' => $permission->id,
            'user_id' => $user->id,
        ]);
    }
    
    public function testCanUpdatePermissionWhenAdminAndWithValidClass()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
        
        $permission = Permission::factory()->create();
        
        $permission = PermissionService::new()->updatePermission(
            PermissionDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => "company",
                'permissionId' => $permission->id
            ])
        );

        $this->assertDatabaseHas('permissions', [
            'name' => $permission->name,
            'class' => $permission->class,
            'id' => $permission->id,
        ]);
    }
    
    public function testCanUpdatePermissionWhenAdminAndNullableClass()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
        
        $permission = Permission::factory()->create();

        $this->assertDatabaseHas('permissions', [
            'name' => $permission->name,
            'class' => $permission->class,
            'id' => $permission->id,
        ]);
        
        $permission = PermissionService::new()->updatePermission(
            PermissionDTO::new()->fromArray([
                'name' => $this->faker->name(),
                'user' => $user,
                'class' => null,
                'permissionId' => $permission->id
            ])
        );

        $this->assertDatabaseHas('permissions', [
            'name' => $permission->name,
            'class' => null,
            'id' => $permission->id,
        ]);
    }

    public function testCannotDeletePermissionWithoutUser()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
 
        $permission = Permission::factory()->create();

        PermissionService::new()->deletePermission(
            PermissionDTO::new()->fromArray([
                'permissionId' => $permission->id
            ])
        );
    }
    
    public function testCannotDeletePermissionWhenNotASuperAdmin()
    {
        $this->expectException(ServiceException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::ADMIN
            ]), [], 'userTypes')
            ->create();
            
        $this->expectExceptionMessage("Sorry! For this action, {$user->name} must be a super administrator.");
        
        $permission = Permission::factory()->create();

        PermissionService::new()->deletePermission(
            PermissionDTO::new()->fromArray([
                'user' => $user,
                'permissionId' => $permission->id
            ])
        );
    }
    
    public function testCannotDeletePermissionWithoutPermissionId()
    {
        $this->expectException(PermissionException::class);

        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $this->expectExceptionMessage("Sorry! A permission is required to perform this action.");
        
        PermissionService::new()->deletePermission(
            PermissionDTO::new()->fromArray([
                'user' => $user,
            ])
        );
    }
    
    public function testCanDeletePermissionWhenSuperAdmin()
    {
        $user = User::factory()
            ->hasAttached(UserType::factory([
                'name' => UserType::SUPERADMIN
            ]), [], 'userTypes')
            ->create();
        
        $permission = Permission::factory()->create([
            'class' => "App\\Models\\Company"
        ]);

        $this->assertDatabaseHas('permissions', [
            'name' => $permission->name,
            'class' => $permission->class,
            'id' => $permission->id,
            'user_id' => $user->id,
        ]);
        
        PermissionService::new()->deletePermission(
            PermissionDTO::new()->fromArray([
                'user' => $user,
                'permissionId' => $permission->id
            ])
        );

        $this->assertDatabaseMissing('permissions', [
            'name' => $permission->name,
            'class' => $permission->class,
            'id' => $permission->id,
            'user_id' => $user->id,
        ]);
    }
    
    public function testCanDeletePermissionAndRemoveAllAuthorizationsAndDetachFromRolesWhenSuperAdmin()
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
        $authorization->authorization()->associate($permission);
        $authorization->save();


        $role->permissions()->attach($permission);

        $this->assertDatabaseHas('permissions', [
            'name' => $permission->name,
            'class' => $permission->class,
            'id' => $permission->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('authorizations', [
            'authorization_type' => $permission::class,
            'authorization_id' => $permission->id,
            'authorizable_type' => $other::class,
            'authorizable_id' => $other->id,
        ]);

        $this->assertDatabaseHas('permission_role', [
            'permission_id' => $permission->id,
            'role_id' => $user->id,
        ]);
        
        PermissionService::new()->deletePermission(
            PermissionDTO::new()->fromArray([
                'user' => $user,
                'permissionId' => $permission->id
            ])
        );

        $this->assertDatabaseMissing('permissions', [
            'name' => $permission->name,
            'class' => $permission->class,
            'id' => $permission->id,
            'user_id' => $user->id,
        ]);
    }

    public function testCannotsyncPermissionsAndRoleWithoutUser()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Sorry! A valid user is required to perform this action.');
 
        $permission = Permission::factory()->create();

        PermissionService::new()->syncPermissionsAndRole(
            PermissionDTO::new()
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
        
        PermissionService::new()->syncPermissionsAndRole(
            PermissionDTO::new()->fromArray([
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
        
        $this->expectExceptionMessage("Sorry! You are required to provide id of permissions.");
        
        PermissionService::new()->syncPermissionsAndRole(
            PermissionDTO::new()->fromArray([
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
        
        PermissionService::new()->syncPermissionsAndRole(
            PermissionDTO::new()->fromArray([
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
        
        $this->expectExceptionMessage("Sorry! You are required to provide id of permissions.");
        
        PermissionService::new()->syncPermissionsAndRole(
            PermissionDTO::new()->fromArray([
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
        
        PermissionService::new()->syncPermissionsAndRole(
            PermissionDTO::new()->fromArray([
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
        
        PermissionService::new()->syncPermissionsAndRole(
            PermissionDTO::new()->fromArray([
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
        
        PermissionService::new()->syncPermissionsAndRole(
            PermissionDTO::new()->fromArray([
                'user' => $user,
                'roleId' => $role->id,
                'permissionIds' => [1, 2, 3, 4]
            ])
        );
    }
    
    public function testCansyncPermissionsAndRoleWithPermissionIdsHavingSameClassWhenAdmin()
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
        
        PermissionService::new()->syncPermissionsAndRole(
            PermissionDTO::new()->fromArray([
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
    
    public function testCansyncPermissionsAndRoleWithPermissionIdsHavingSameClassWhenOwner()
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
        
        PermissionService::new()->syncPermissionsAndRole(
            PermissionDTO::new()->fromArray([
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
    
    public function testCansyncPermissionsAndRoleThatHasANullClassWithPermissionIdsHavingAnyClassWhenAdmin()
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
        
        PermissionService::new()->syncPermissionsAndRole(
            PermissionDTO::new()->fromArray([
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
    
    public function testCansyncPermissionsAndRoleThatHasANullClassWithPermissionIdsHavingAnyClassWhenOwner()
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
        
        PermissionService::new()->syncPermissionsAndRole(
            PermissionDTO::new()->fromArray([
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
    
    public function testCansyncPermissionsAndRoleWithPermissionIdsHavingSameAndNullClassesWhenAdmin()
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
        
        PermissionService::new()->syncPermissionsAndRole(
            PermissionDTO::new()->fromArray([
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
    
    public function testCansyncPermissionsAndRoleWithPermissionIdsHavingSameAndNullClassesWhenOwner()
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
        
        PermissionService::new()->syncPermissionsAndRole(
            PermissionDTO::new()->fromArray([
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
}
