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
use App\Handlers\TaskIndexHandler;

class TaskController extends Controller
{
    /**
     * @param TaskService $taskService
     */
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    /**
     * @param Request $request
     * @param TaskIndexHandler $handler
     * @return View
     */
    public function index(Request $request, TaskIndexHandler $handler): View
    {
        $data = $handler->handle($request);

        return view('tasks.index', $data);
    }

    /**
     * @return View
     */
    public function create(): View
    {
        return view('tasks.create');
    }

    /**
     * @param StoreTaskRequest $request
     * @return RedirectResponse
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->taskService->createTask($validated);

        return redirect()->route('tasks.index')
            ->with('success', 'Вы создали новую задачу!');
    }


    /**
     * @param Task $task
     * @return View
     */
    public function edit(Task $task): View
    {
        return view('tasks.edit', compact('task'));
    }

    /**
     * @param UpdateTaskRequest $request
     * @param Task $task
     * @return RedirectResponse
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $validated = $request->validated();
        $this->taskService->updateTask($task, $validated);

        return redirect()->route('tasks.index')
            ->with('success', 'Задача успешно обновлена!');
    }


    /**
     * @param Task $task
     * @return RedirectResponse
     */
    public function destroy(Task $task): RedirectResponse
    {
        $this->taskService->deleteTask($task);

        return redirect()->route('tasks.index')
            ->with('success', 'Задача удалена!');
    }


    /**
     * @param $id
     * @return RedirectResponse
     */
    public function restore($id): RedirectResponse
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $this->taskService->restoreTask($task);

        return redirect()->route('tasks.index', ['show_deleted' => true])
            ->with('success', 'Задача восстановлена!');
    }


    /**
     * @param $id
     * @return RedirectResponse
     */
    public function forceDelete($id): RedirectResponse
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $this->taskService->forceDeleteTask($task);

        return redirect()->route('tasks.index', ['show_deleted' => true])
            ->with('success', 'Задача удалена окончательно!');
    }

    /**
     * @param ToggleTaskRequest $request
     * @param Task $task
     * @return RedirectResponse
     */

    public function toggle(ToggleTaskRequest $request, Task $task): RedirectResponse
    {
        $this->taskService->toggleTaskCompletion($task);

        return redirect()->route('tasks.index')
            ->with('success', 'Обновлен статус задачи');
    }
}
