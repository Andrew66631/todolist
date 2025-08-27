<?php

namespace App\Http\Controllers;

use App\Services\TaskService;
use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\ToggleTaskRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $showDeleted = $request->boolean('show_deleted');
        $filters = [
            'tags' => $request->input('tags', ''),
            'completed' => $request->input('completed'),
            'show_deleted' => $showDeleted,
        ];

        if ($showDeleted) {
            $tasks = Task::onlyTrashed()
                ->when($search, function($query, $search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                })
                ->when($filters['tags'], function($query, $tags) {
                    $query->withTags($tags); // Используем исправленный scope
                })
                ->when(isset($filters['completed']), function($query) use ($filters) {
                    $query->where('completed', $filters['completed']);
                })
                ->latest()
                ->paginate(10);
        } else {
            $tasks = $this->taskService->getPaginatedTasks($search, $filters, 10);
        }

        $tasks->appends([
            'search' => $search,
            'tags' => $filters['tags'],
            'completed' => $filters['completed'],
            'show_deleted' => $showDeleted
        ]);

        return view('tasks.index', [
            'tasks' => $tasks,
            'search' => $search,
            'filters' => $filters,
            'showDeleted' => $showDeleted,
        ]);
    }

    public function create(): View
    {
        return view('tasks.create');
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->taskService->createTask($validated);

        return redirect()->route('tasks.index')
            ->with('success', 'Вы создали новую задачу!');
    }

    public function edit(Task $task): View
    {
        return view('tasks.edit', compact('task'));
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $validated = $request->validated();

        $this->taskService->updateTask($task, $validated);

        return redirect()->route('tasks.index')
            ->with('success', 'Задача успешно обновлена!');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->taskService->deleteTask($task);

        return redirect()->route('tasks.index')
            ->with('success', 'Задача удалена!');
    }

    public function restore($id): RedirectResponse
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $this->taskService->restoreTask($task);

        return redirect()->route('tasks.index', ['show_deleted' => true])
            ->with('success', 'Задача восстановлена!');
    }

    public function forceDelete($id): RedirectResponse
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $this->taskService->forceDeleteTask($task);

        return redirect()->route('tasks.index', ['show_deleted' => true])
            ->with('success', 'Задача удалена окончательно!');
    }

    public function toggle(ToggleTaskRequest $request, Task $task): RedirectResponse
    {
        $this->taskService->toggleTaskCompletion($task);

        return redirect()->route('tasks.index')
            ->with('success', 'Обновлен статус задачи');
    }
}
