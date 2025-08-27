<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{

    /**
     * @param int $id
     * @return Task|null
     */
    public function getById(int $id): ?Task
    {
        return Task::find($id);
    }

    /**
     * @param int $userId
     * @param string|null $search
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
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


    /**
     * @param array $data
     * @return Task
     */

    public function create(array $data): Task
    {
        return Task::create($data);
    }


    /**
     * @param Task $task
     * @param array $data
     * @return bool
     */
    public function update(Task $task, array $data): bool
    {
        return $task->update($data);
    }


    /**
     * @param Task $task
     * @return bool
     */
    public function delete(Task $task): bool
    {
        return $task->delete();
    }


    /**
     * @param Task $task
     * @return bool
     */
    public function forceDelete(Task $task): bool
    {
        return $task->forceDelete();
    }


    /**
     * @param Task $task
     * @return bool
     */
    public function restore(Task $task): bool
    {
        return $task->restore();
    }


    /**
     * @param string|null $search
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getDeletedPaginated(?string $search = null, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Task::onlyTrashed()->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['tags'])) {
            $query->withTags($filters['tags']);
        }

        if (isset($filters['completed'])) {
            $query->where('completed', $filters['completed']);
        }

        return $query->paginate($perPage);
    }


    /**
     * @param string|null $search
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(?string $search = null, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Task::latest();

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


    /**
     * @return Collection
     */
    public function all(): Collection
    {
        return Task::all();
    }
}
