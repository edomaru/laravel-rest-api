<?php

namespace Tests\Feature\Api\V1;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_list_of_tasks(): void
    {
        // Arrange: create 2 fake tasks
        $tasks = Task::factory()->count(2)->create();

        // Act: Make a GET request to the endpoint
        $response = $this->getJson('/api/v1/tasks');

        // Assert: status is 200 OK and data has 2 items
        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonStructure([
            'data' => [
                ['id', 'name', 'is_completed']
            ]
        ]);
    }

    public function test_user_can_get_single_task(): void
    {
        // Arange: crate a task
        $task = Task::factory()->create();

        // Act: Make a GET request to the endpoint with task ID
        $response = $this->getJson('/api/v1/tasks/' . $task->id);

        // Assert: response contains the correct task data
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => ['id', 'name', 'is_completed']
        ]);
        $response->assertJson([
            'data' => [
                'id' => $task->id,
                'name' => $task->name,
                'is_completed' => $task->is_completed,
            ]
        ]);
    }
}
