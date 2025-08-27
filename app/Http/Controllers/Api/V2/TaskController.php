<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if ($request->user()->cannot('viewAny', Task::class)) {
            abort(403);
        }

        // return TaskResource::collection(Task::all());
        return request()->user()
            ->tasks()
            ->get()
            ->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        if ($request->user()->cannot('create', Task::class)) {
            abort(403);
        }
        
        $task = $request->user()->tasks()->create($request->validated());

        return $task->toResource();
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task, Request $request)
    {
        if ($request->user()->cannot('view', $task)) {
            abort(403);
        }

        return $task->toResource();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        if ($request->user()->cannot('update', $task)) {
            abort(403);
        }

        $task->update($request->validated());

        return $task->toResource();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task, Request $request)
    {
        if ($request->user()->cannot('delete', $task)) {
            abort(403);
        }

        $task->delete();

        return response()->noContent();
    }
}
