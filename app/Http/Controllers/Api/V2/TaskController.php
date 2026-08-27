<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[Authorize('viewAny', Task::class)]
    public function index()
    {
        // return TaskResource::collection(Task::all());
        return request()->user()
            ->tasks()
            ->get()
            ->toResourceCollection();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    #[Authorize('create', Task::class)]
    public function store(StoreTaskRequest $request)
    {
        // $task = Task::create($request->validated() + ['user_id' => $request->user()->id]);
        $task = $request->user()->tasks()->create($request->validated());

        return $task->toResource();
    }

    /**
     * Display the specified resource.
     */
    #[Authorize('view', 'task')]
    public function show(Task $task)
    {
        // return new TaskResource($task);
        // return TaskResource::make($task);
        return $task->toResource();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    #[Authorize('update', 'task')]
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->update($request->validated());

        return $task->toResource();
    }

    /**
     * Remove the specified resource from storage.
     */
    #[Authorize('delete', 'task')]
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->noContent();
    }
}
