<?php

namespace Tests\Feature\Api\V2;

use App\Models\Priority;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskNaturalInputTest extends TestCase
{
    use RefreshDatabase;
 
    protected User $user;
 
    protected function setUp(): void
    {
        parent::setUp();
 
        $this->user = User::factory()->create();
 
        $this->actingAs($this->user);
    }
 
    public function test_user_can_create_task_with_natural_input()
    {
        $response = $this->postJson('/api/v2/tasks', [
            'name' => 'Do laundry @today !low'
        ]);
 
        $response->assertCreated();
        
        $this->assertDatabaseHas('tasks', [
            'user_id' => $this->user->id,
            'name' => 'Do laundry',
            'priority_id' => Priority::whereName('low')->value('id'),
        ]);
 
        $this->assertNotNull($response['data']['due_date']);
    }
    
    public function test_user_can_create_task_without_natural_input()
    {
        $response = $this->postJson('/api/v2/tasks', [
            'name' => 'Read a Book',
            'priority_id' => $priorityId = Priority::whereName('low')->value('id'),
            'due_date' => now()->format("Y-m-d")
        ]);
 
        $response->assertCreated();
 
        $this->assertDatabaseHas('tasks', [
            'user_id' => $this->user->id,
            'name' => 'Read a Book',
            'priority_id' => $priorityId,
        ]);
 
        $this->assertNotNull($response['data']['due_date']);
    }
 
    public function test_natural_input_overrided_with_explicit_values()
    {
        $mediumId = Priority::whereName('medium')->value('id');
 
        $response = $this->postJson('/api/v2/tasks', [
            'name' => 'Override Task !high @today',
            'priority_id' => $mediumId,
            'due_date' => now()->addWeek()->format('Y-m-d'),
        ]);
 
        $response->assertCreated();
 
        $this->assertDatabaseHas('tasks', [
            'name' => 'Override Task',
            'priority_id' => $mediumId,
        ]);
 
        $this->assertTrue(now()->addWeek()->isSameDay($response->json('data.due_date')));
    }
}
