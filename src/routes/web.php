<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('tasks.index');
});

Route::resource('tasks', TaskController::class);
Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
Route::patch('tasks/{id}/restore', [TaskController::class, 'restore'])->name('tasks.restore');
Route::delete('tasks/{id}/force-delete', [TaskController::class, 'forceDelete'])->name('tasks.forceDelete');
