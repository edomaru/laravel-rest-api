<?php

namespace Tests\Unit;

use App\Models\Priority;
use App\Services\TaskInputParser;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskInputParserTest extends TestCase
{
    use RefreshDatabase;

    protected TaskInputParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new TaskInputParser();
    }

    public function test_it_parses_task_name_priority_and_today_due_date()
    {
        $result = $this->parser->parse("Finish assignment @today !high");

        $this->assertEquals('Finish assignment', $result['name']);
        
        $this->assertEquals(
            'Finish assignment',
            $result['name']
        );

        $this->assertEquals(
            now()->format('Y-m-d'),
            $result['due_date']->format('Y-m-d')
        );
        
        $this->assertEquals(
            Priority::where('name', 'high')->value('id'),
            $result['priority_id']
        );
    }
    
    public function test_it_parses_task_with_custom_date_and_medium_priority()
    {
        $result = $this->parser->parse("Go hiking !medium @2025-08-30");

        $this->assertEquals('Go hiking', $result['name']);
        
        $this->assertEquals(
	        '2025-08-30', 
	        Carbon::parse($result['due_date'])->format('Y-m-d')
	      );
	      
	      $this->assertEquals(
            Priority::where('name', 'medium')->value('id'),
            $result['priority_id']
        );
    }
    
    public function test_it_handles_missing_priority_and_due_date()
    {
        $result = $this->parser->parse("Just a task with no extras");

        $this->assertEquals('Just a task with no extras', $result['name']);
        $this->assertNull($result['priority_id']);
        $this->assertNull($result['due_date']);
    }

    public function test_it_returns_null_if_name_is_missing()
    {
        $result = $this->parser->parse("@today !high");

        $this->assertNull($result);
    }
}
