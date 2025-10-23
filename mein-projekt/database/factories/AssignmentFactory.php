<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => \App\Models\Employee::factory(),
            'project_id' => \App\Models\Project::factory(),
            'task_name' => $this->faker->sentence(3),
            'task_description' => $this->faker->paragraph,
            'weekly_hours' => $this->faker->randomFloat(2, 10, 40),
            'start_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+2 months', '+6 months'),
            'priority_level' => $this->faker->numberBetween(1, 5),
            'display_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
