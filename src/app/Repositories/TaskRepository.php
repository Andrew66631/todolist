<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository
{
    public function getById(int $id): ?Task
    {
        return Task::find($id);
    }

    public function getByUserWithFilters(int $userId, ?string $search = null, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Task::where('user_id', $userId)->latest();

        if ($search) {
            $query->search($search);
        }

        if (!empty($filters['tags'])) {
            $query->withTags($filters['tags']);
        }

        if (isset($filters['completed'])) {
            $query->where('completed', $filters['completed']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): bool
    {
        return $task->update($data);
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }
}
