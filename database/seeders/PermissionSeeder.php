<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create permissions

        Permission::factory()
            ->count(24)
            ->state(new Sequence(
                ["name" => "update", "class" => Project::class, "description" => "default will be have: owner, admin, "],
                ["name" => "delete", "class" => Project::class, "description" => "default will be have: owner, admin, "],

                ["name" => "create session", "class" => Project::class, "description" => "default will be have: owner, admin, facilitator"],
                ["name" => "update session", "class" => Project::class, "description" => "default will be have: owner, admin, facilitator"],
                ["name" => "delete session", "class" => Project::class, "description" => "default will be have: owner, admin, facilitator"],

                ["name" => "create held session", "class" => Project::class, "description" => "default will be have: owner, admin, facilitator"],
                ["name" => "update held session", "class" => Project::class, "description" => "default will be have: owner, admin, facilitator"],
                ["name" => "delete held session", "class" => Project::class, "description" => "default will be have: owner, admin, facilitator"],

                ["name" => "add learner", "class" => Project::class, "description" => "default will be have: owner, admin, facilitator"],
                ["name" => "remove learner", "class" => Project::class, "description" => "default will be have: owner, admin, facilitator"],
                ["name" => "ban learner", "class" => Project::class, "description" => "default will be have: owner, admin, facilitator"],

                ["name" => "add facilitator", "class" => Project::class, "description" => "default will be have: owner, admin, "],
                ["name" => "remove facilitator", "class" => Project::class, "description" => "default will be have: owner, admin, "],
                ["name" => "ban facilitator", "class" => Project::class, "description" => "default will be have: owner, admin, "],

                ["name" => "add skills to project", "class" => Project::class, "description" => "default will be have: owner, admin, facilitator"],

                ["name" => "add member", "class" => Company::class, "description" => "default will be have: owner, admin, administrator"],
                ["name" => "remove member", "class" => Company::class, "description" => "default will be have: owner, admin, administrator"],
                ["name" => "ban member", "class" => Company::class, "description" => "default will be have: owner, admin, administrator"],
                ["name" => "add administrator", "class" => Company::class, "description" => "default will be have: owner, admin, "],
                ["name" => "remove administrator", "class" => Company::class, "description" => "default will be have: owner, admin, "],
                ["name" => "ban administrator", "class" => Company::class, "description" => "default will be have: owner, admin, "],

                ["name" => "create roles", "class" => null, "description" => "default will be have: owner, admin, administrator, "],
                ["name" => "assign roles", "class" => null, "description" => "default will be have: owner, admin, administrator, "],
                ["name" => "assign permissions", "class" => null, "description" => "default will be have: owner, admin, administrator, "],
            ))
            ->create([
                "user_id" => 1
            ]);
    }
}
