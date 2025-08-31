<?php

namespace Database\Factories;

use App\Models\Priority;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(),
            'is_completed' => rand(0, 1)
        ];
    }

    public function withPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority_id' => Priority::pluck('id')->random(),
        ]);
    }

    public function withRandomPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority_id' => rand(0, 1) === 0 ? NULL : Priority::pluck('id')->random(),
        ]);
    }
}
