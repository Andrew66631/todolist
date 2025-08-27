<?php

namespace App\Services;

use App\Repositories\TaskRepositoryInterface;
use App\Models\Task;
use App\Jobs\ProcessTaskCompletion;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class TaskService
{

    /**
     * @param TaskRepositoryInterface $taskRepository
     */
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {}


    /**
     * @param string|null $search
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedTasks(?string $search = null, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $cacheKey = $this->getCacheKey($search, $filters, $perPage);

        return Cache::remember($cacheKey, 3600, function() use ($search, $filters, $perPage) {
            return $this->taskRepository->getPaginated($search, $filters, $perPage);
        });
    }


    /**
     * @param string|null $search
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getDeletedPaginatedTasks(?string $search = null, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->taskRepository->getDeletedPaginated($search, $filters, $perPage);
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
        return $this->taskRepository->getByUserWithFilters($userId, $search, $filters, $perPage);
    }


    /**
     * @param array $data
     * @return Task
     */

    public function createTask(array $data): Task
    {
        $task = $this->taskRepository->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'tags' => $data['tags'] ?? [],
            'user_id' => $data['user_id'] ?? auth()->id(),
        ]);

        $this->clearCache();

        return $task;
    }


    /**
     * @param Task $task
     * @param array $data
     * @return Task
     */
    public function updateTask(Task $task, array $data): Task
    {
        $this->taskRepository->update($task, [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'tags' => $data['tags'] ?? [],
            'completed' => $data['completed'] ?? $task->completed,
        ]);

        if (isset($data['completed'])) {
            ProcessTaskCompletion::dispatch($task, $data['completed']);
        }

        $this->clearCache();

        return $task->fresh();
    }


    /**
     * @param Task $task
     * @return void
     */

    public function deleteTask(Task $task): void
    {
        $this->taskRepository->delete($task);
        $this->clearCache();
    }


    /**
     * @param Task $task
     * @return Task
     */
    public function toggleTaskCompletion(Task $task): Task
    {
        $completed = !$task->completed;

        $this->taskRepository->update($task, ['completed' => $completed]);
        ProcessTaskCompletion::dispatch($task, $completed);

        $this->clearCache();

        return $task->fresh();
    }


    /**
     * @param Task $task
     * @return void
     */

    public function forceDeleteTask(Task $task): void
    {
        $this->taskRepository->forceDelete($task);
        $this->clearCache();
    }


    /**
     * @param Task $task
     * @return void
     */

    public function restoreTask(Task $task): void
    {
        $this->taskRepository->restore($task);
        $this->clearCache();
    }


    /**
     * @param int $id
     * @return Task|null
     */

    public function findTask(int $id): ?Task
    {
        return $this->taskRepository->getById($id);
    }


    /**
     * @param string|null $search
     * @param array $filters
     * @param int $perPage
     * @return string
     */
    private function getCacheKey(?string $search, array $filters, int $perPage): string
    {
        return sprintf(
            'tasks_%s_%s_%d',
            $search ? md5($search) : 'all',
            md5(serialize($filters)),
            $perPage
        );
    }


    /**
     * @return void
     */
    private function clearCache(): void
    {
        $store = Cache::getStore();

        if (method_exists($store, 'getRedis')) {
            try {
                $redis = $store->getRedis();
                $prefix = config('database.redis.options.prefix', '');

                $keys = $redis->keys($prefix . 'tasks_*');
                foreach ($keys as $key) {
                    $cleanKey = str_replace($prefix, '', $key);
                    Cache::forget($cleanKey);
                }
            } catch (\Exception $e) {
                Cache::flush();
            }
        } else {
            Cache::flush();
        }
    }
}
