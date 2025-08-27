<?php

namespace App\Handlers;

use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskIndexHandler
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function handle(Request $request): array
    {
        $search = $request->input('search');
        $showDeleted = $request->boolean('show_deleted');
        $filters = [
            'tags' => $request->input('tags', ''),
            'completed' => $request->input('completed'),
            'show_deleted' => $showDeleted,
        ];

        if ($showDeleted) {
            $tasks = $this->taskService->getDeletedPaginatedTasks($search, $filters, 10);
        } else {
            $tasks = $this->taskService->getPaginatedTasks($search, $filters, 10);
        }

        $tasks->appends([
            'search' => $search,
            'tags' => $filters['tags'],
            'completed' => $filters['completed'],
            'show_deleted' => $showDeleted
        ]);

        return [
            'tasks' => $tasks,
            'search' => $search,
            'filters' => $filters,
            'showDeleted' => $showDeleted,
        ];
    }
}
