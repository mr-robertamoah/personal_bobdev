<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "name" => $this->faker->name,
            "description" => $this->faker->sentence(),
            "class" => random_int(0, 1) ? "App\\Models\\Project" : "App\\Models\\Company",
            "public" => random_int(0, 3) ? 1 : 0,
            'user_id' => 1
        ];
    }
}
