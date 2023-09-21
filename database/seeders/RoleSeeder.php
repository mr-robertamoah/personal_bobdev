<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create facilitator roles
        $role = Role::factory()
            ->state([
                'name' => "default facilitator",
                "user_id" => 1
            ])
            ->create();

        $permissions = Permission::query()
            ->where("description", "LIKE", "%facilitator%")
            ->get(['id']);

        $role->permissions()->attach($permissions);

        // create administrator roles
        $role = Role::factory()
            ->state([
                'name' => "default administrator",
                "user_id" => 1
            ])
            ->create();

        $permissions = Permission::query()
            ->where("description", "LIKE", "%administrator%")
            ->get(['id']);

        $role->permissions()->attach($permissions);
    }
}
