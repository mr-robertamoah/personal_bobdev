<?php

namespace Database\Seeders;

use App\Models\UserType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserType::factory()
            ->count(5)
            ->state(new Sequence(
                // ['name' => 'SUPERADMIN', 'description' => 'This is the account with all the abilities'],
                ['name' => 'ADMIN', 'description' => 'You will have supervisory privileges that will make you do things normal users cannot do.'],
                ['name' => 'PARENT', 'description' => 'This will give you the ability to add accounts of wards and also determine which course your wards take.'],
                ['name' => 'STUDENT', 'description' => 'This will help you take course on the platform'],
                ['name' => 'DONOR', 'description' => 'This will help you donate items to institutions that run our programs as well as to students. You will be able to sponsor our programs as well.'],
                ['name' => 'FACILITATOR', 'description' => 'This will make you join our team of facilitators who will help various students learn several skills.'],
            ))
            ->create(['user_id' => 1]);
    }
}
