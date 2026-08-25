<?php

namespace Tests\Feature\Api\V2;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskDueDateTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->actingAs($this->user);
    }

    public function test_user_can_create_task_with_due_date(): void
    {
        $dueDate = now()->addDay()->startOfSecond();

        $response = $this->postJson('/api/v2/tasks', [
            'name' => $name = 'Task with due date',
            'due_date' => $dueDate->toDateTimeString(),
        ]);

        $response->assertCreated();
        $response->assertJsonFragment([
            'due_date' => $dueDate->toJSON()
        ]);
        $this->assertDatabaseHas('tasks', [
            'name' => $name,
            'due_date' => $dueDate->toDateTimeString(),
        ]);
    }

    public function test_user_can_create_task_without_due_date(): void
    {
        $response = $this->postJson('/api/v2/tasks', [
            'name' => 'Task without due date',
            // no due_date provided
        ]);
    
        $response->assertCreated();
        $response->assertJsonFragment([
            'due_date' => null
        ]);
        $this->assertDatabaseHas('tasks', [
            'name' => 'Task without due date',
            'due_date' => null,
        ]);
    }

    public function test_it_can_filter_tasks_due_today()
    {
        // Today’s task
        Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()
        ]);
    
        // Future task
        Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDay(),
        ]);
    
        // Null due date
        Task::factory()->create([
            'user_id' => $this->user->id,
        ]);
    
        $this->getJson('/api/v2/tasks?due_date=today')
            ->assertOk()
            ->assertJsonCount(2, 'data'); // today + null
    }

    public function test_it_can_filter_overdue_tasks()
    {
        // Overdue task
        Task::factory()
            ->withDueDate(now()->subDay())
            ->create(['user_id' => $this->user->id]);
    
        // Today’s task
        Task::factory()
            ->withDueDate(now())
            ->create(['user_id' => $this->user->id]);
    
        // Future task
        Task::factory()
            ->withDueDate(now()->addDay())
            ->create(['user_id' => $this->user->id]);
    
        // Null due date
        Task::factory()
                ->create(['user_id' => $this->user->id]);
    
        $this->getJson('/api/v2/tasks?due_date=overdue')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_it_returns_all_tasks_without_due_date_filter()
    {
        // Overdue task
        Task::factory()
            ->withDueDate(now()->subDay())
            ->create(['user_id' => $this->user->id]);
    
        // Today’s task
        Task::factory()
            ->withDueDate(now())
            ->create(['user_id' => $this->user->id]);
    
        // Future task
        Task::factory()
            ->withDueDate(now()->addDay())
            ->create(['user_id' => $this->user->id]);
    
        // Null due date
        Task::factory()
            ->create(['user_id' => $this->user->id]);
    
        $this->getJson('/api/v2/tasks')
            ->assertOk()
            ->assertJsonCount(4, 'data');
    }
}
