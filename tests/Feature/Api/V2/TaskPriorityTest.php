<?php

namespace Tests\Feature\Api\V2;

use App\Models\Task;
use App\Models\User;
use App\Models\Priority;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskPriorityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_task_with_priority(): void
    {
        $user = User::factory()->create();
        $priority = Priority::first();

        $response = $this->actingAs($user)->postJson('/api/v2/tasks', [
            'name' => 'Finish homework',
            'priority_id' => $priority->id
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('tasks', [
            'name' => 'Finish homework',
            'priority_id' => $priority->id
        ]);
    }

    public function test_user_can_create_task_without_priority(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v2/tasks', [
            'name' => 'Finish homework',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('tasks', [
            'name' => 'Finish homework',
            'priority_id' => null
        ]);
    }

    public function test_user_can_update_task_priority(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();
        $priority = Priority::first();

        $response = $this->actingAs($user)->putJson("/api/v2/tasks/{$task->id}", [
            'name' => $task->name,
            'priority_id' => $priority->id
        ]);

        $response->assertOk();
        $this->assertEquals($priority->id, $task->fresh()->priority_id);
    }

    public function test_prioritized_task_returns_priority(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->withPriority()->create();

        $response = $this->actingAs($user)->getJson("/api/v2/tasks/{$task->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.priority.id', $task->priority->id);
    }
    
    public function test_unprioritized_task_returns_priority(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->getJson("/api/v2/tasks/{$task->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.priority', null);
    }

    public function test_validation_fails_with_invalid_priority(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v2/tasks', [
            'name' => 'Write test cases',
            'priority_id' => 9999
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('priority_id');
    }

    public function test_tasks_are_sorted_by_priority(): void
    {
        $user = User::factory()->create();

        // Create three tasks with different priorities
        $low = Priority::whereName('low')->first();
        $high = Priority::whereName('high')->first();
        $medium = Priority::whereName('medium')->first();

        Task::factory()->for($user)->create(['priority_id' => $low->id]);
        Task::factory()->for($user)->create(['priority_id' => $high->id]);
        Task::factory()->for($user)->create(['priority_id' => $medium->id]);
        Task::factory()->for($user)->create(['priority_id' => null]);

        $response = $this->actingAs($user)->getJson('/api/v2/tasks?sort_by=priority');

        $response->assertOk();

        $priorities = collect($response->json('data'))
            ->pluck('priority.name')
            ->filter()
            ->values();

        $this->assertEquals(['high', 'medium', 'low'], $priorities->toArray());
    }
}
