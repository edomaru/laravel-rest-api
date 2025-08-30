<?php

namespace Tests\Feature\Api\V2;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskSummaryTest extends TestCase
{
    use RefreshDatabase;

    private function createTask(User $user, array $overrides = [])
    {
        return Task::factory()->create(array_merge([
            'user_id' => $user->id,
            'created_at' => now(),
        ], $overrides));
    }

    public function test_it_returns_tasks_created_today()
    {
        $user = User::factory()->create();

        // Task created today — should appear
        $this->createTask($user, ['created_at' => now(), 'name' => 'Today Task']);

        // Task from yesterday — should NOT appear
        $this->createTask($user, ['created_at' => now()->subDay(), 'name' => 'Old Task']);

        $response = $this->actingAs($user)->getJson('/api/v2/summaries?period=today');

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Today Task']);
        $response->assertJsonMissing(['name' => 'Old Task']);
    }

    public function test_it_returns_tasks_created_last_week()
    {
        $user = User::factory()->create();

        // Task inside last week’s range -> should appear
        $this->createTask($user, [
            'created_at' => now()->subWeek()->startOfWeek()->addDay(),
            'name' => 'Last Week Task'
        ]);

        // Task from this week -> should NOT appear
        $this->createTask($user, [
            'created_at' => now()->startOfWeek()->addDay(),
            'name' => 'This Week Task'
        ]);

        $response = $this->actingAs($user)->getJson('/api/v2/summaries?period=last-week');

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Last Week Task']);
        $response->assertJsonMissing(['name' => 'This Week Task']);
    }

    public function test_it_returns_tasks_from_this_month()
    {
        $user = User::factory()->create();

        // Task from current month -> should appear
        $this->createTask($user, [
            'created_at' => now()->startOfMonth()->addDays(5),
            'name' => 'Monthly Task'
        ]);

        // Task from last month -> should NOT appear
        $this->createTask($user, [
            'created_at' => now()->startOfMonth()->subMonth(),
            'name' => 'Old Monthly Task'
        ]);

        $response = $this->actingAs($user)->getJson('/api/v2/summaries?period=this-month');

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Monthly Task']);
        $response->assertJsonMissing(['name' => 'Old Monthly Task']);
    }

    public function test_it_uses_this_week_as_default_period_if_none_provided()
    {
        $user = User::factory()->create();

        // Task from this week -> should appear
        $this->createTask($user, [
            'created_at' => now()->startOfWeek()->addDay(),
            'name' => 'This Week Task'
        ]);

        // Task from two weeks ago -> should NOT appear
        $this->createTask($user, [
            'created_at' => now()->subWeeks(2),
            'name' => 'Very Old Task'
        ]);

        $response = $this->actingAs($user)->getJson('/api/v2/summaries');

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'This Week Task']);
        $response->assertJsonMissing(['name' => 'Very Old Task']);
    }
}
