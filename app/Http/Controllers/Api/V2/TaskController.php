<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Task;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
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
        // $task = Task::create($request->validated() + ['user_id' => $request->user()->id]);
        $task = $request->user()->tasks()->create($request->validated());

        return $task->toResource();
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        // return new TaskResource($task);
        // return TaskResource::make($task);
        return $task->toResource();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->update($request->validated());

        return $task->toResource();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->noContent();
    }
}
