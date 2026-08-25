<?php

use App\Http\Controllers\Api\V2\CompleteTaskController;
use App\Http\Controllers\Api\V2\PriorityController;
use App\Http\Controllers\Api\V2\SummaryController;
use App\Http\Controllers\Api\V2\TaskController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/tasks', TaskController::class);
Route::patch('/tasks/{task}/complete', CompleteTaskController::class);
Route::get('/summaries', SummaryController::class);
Route::get('/priorities', PriorityController::class);
