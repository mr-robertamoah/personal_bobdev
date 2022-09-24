<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()
            ->state([
                'first_name' => "Robert",
                'surname' => "Amoah",
                'username' => "mr_robertamoah",
                'email' => "mr_robertamoah@yahoo.com",
                'password' => bcrypt('itisme2025')
            ])
            ->hasProfile()
            ->has(UserType::factory()->state(function($attributes, $user) {
                return [
                    'user_id' => $user->id,
                    'name' => 'SUPERADMIN', 'description' => 'This is the account with all the abilities'
                ];
            }))
            ->create();

        User::factory()->count(9)->hasProfile()->create();
    }
}
