<?php

namespace Tests\Feature\Api\V2;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_tasks_owned_by_others()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $task = Task::factory()->for($owner)->create();

        $this->actingAs($otherUser)
            ->getJson("/api/v2/tasks/{$task->id}")
            ->assertForbidden(); // 403
    }

    public function test_user_cannot_update_tasks_owned_by_others()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        $payload = ['name' => 'Unauthorized Update'];

        $this->actingAs($otherUser)
            ->putJson("/api/v2/tasks/{$task->id}", $payload)
            ->assertForbidden();
    }

    public function test_user_cannot_delete_tasks_owned_by_others()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        $this->actingAs($otherUser)
            ->deleteJson("/api/v2/tasks/{$task->id}")
            ->assertForbidden();
    }

    public function test_user_cannot_complete_tasks_owned_by_others()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        $payload = ['is_completed' => true];

        $this->actingAs($otherUser)
            ->patchJson("/api/v2/tasks/{$task->id}/complete", $payload)
            ->assertForbidden();
    }
}
