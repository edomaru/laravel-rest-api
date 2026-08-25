<?php

namespace Database\Factories;

use App\Models\Priority;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
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

    public function withDueDate(Carbon $date): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $date,
        ]);
    }

    public function withRandomDueDate(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => rand(0, 1) === 0 ? 
                NULL : 
                fake()->dateTimeBetween('-1 week', '+1 week'),
        ]);
    }
}
