<?php

namespace Tests\Feature\Api\V2;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_get_list_of_tasks(): void
    {
        // Arrange: create 2 fake tasks
        $user = User::factory()->create();
        $this->actingAs($user);
        $tasks = Task::factory()->count(2)->create([
            'user_id' => $user->id
        ]);

        // Act: Make a GET request to the endpoint
        $response = $this->getJson('/api/v2/tasks');

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
        $user = User::factory()->create();
        $this->actingAs($user);
        $task = Task::factory()->create(['user_id' => $user->id]);

        // Act: Make a GET request to the endpoint with task ID
        $response = $this->getJson('/api/v2/tasks/' . $task->id);

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

    // `POST /tasks` → create a new task
    public function test_user_can_create_a_task(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->postJson('/api/v2/tasks', [
            'name' => 'New task'
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => ['id', 'name', 'is_completed']
        ]);
        $this->assertDatabaseHas('tasks', [
            'name' => 'New task'
        ]);
    }

    public function test_user_cannot_create_invalid_task(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->postJson('/api/v2/tasks', [
            'name' => ''
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    // `PUT /tasks/{id}` → update existing task
    public function test_user_can_update_task(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->putJson('/api/v2/tasks/' . $task->id, [
            'name' => 'Updated Task'
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => 'Updated Task'
        ]);
    }
    
    public function test_user_cannot_update_task_with_invalid_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->putJson('/api/v2/tasks/' . $task->id, [
            'name' => ''
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    // `PATCH /tasks/{id}/complete` → mark the task as completed or incomplete
    public function test_user_can_toggle_task_completion(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $task = Task::factory()->create([
            'is_completed' => false,
            'user_id' => $user->id
        ]);

        $response = $this->patchJson('/api/v2/tasks/' . $task->id . '/complete', [
            'is_completed' => true
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'is_completed' => true
        ]);
    }

    public function test_user_cannot_toggle_completed_with_invalid_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->patchJson('/api/v2/tasks/' . $task->id . '/complete', [
            'is_completed' => 'yes'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['is_completed']);
    }

    // `DELETE /tasks/{id}` → delete a task
    public function test_user_can_delete_task(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson('/api/v2/tasks/' . $task->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }
}
