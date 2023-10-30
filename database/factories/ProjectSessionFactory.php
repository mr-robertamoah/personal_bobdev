<?php

namespace Database\Factories;

use App\Enums\ProjectSessionPeriodEnum;
use App\Enums\ProjectSessionTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectSession>
 */
class ProjectSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $typeValues = ProjectSessionTypeEnum::values();
        $periodValues = ProjectSessionPeriodEnum::values();
        return [
            "name" => $this->faker->name(),
            "description" => $this->faker->sentence(),
            "day_of_week" => random_int(0, 6),
            "type" => ($typeValues[random_int(0, count($typeValues) - 1)]),
            "period" => ($periodValues[random_int(0, count($periodValues) - 1)]),
            "start_date" => now()->addDays(3)->toDateTimeString(),
            "end_date" => now()->subYear()->toDateTimeString(),
            "start_time" => now()->addHour()->toTimeString(),
            "end_time" => now()->addHours(2)->addMinutes(30)->toTimeString(),
        ];
    }
}
