<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteTaskRequest;

class CompleteTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(CompleteTaskRequest $request, Task $task)
    {
        $task->is_completed = $request->is_completed;
        $task->save();

        return $task->toResource();
    }
}
