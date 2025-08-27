<?php

namespace App\Services;

use App\Models\Task;
use App\Jobs\ProcessTaskCompletion;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class TaskService
{
    public function getPaginatedTasks(?string $search = null, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $cacheKey = $this->getCacheKey($search, $filters, $perPage);

        return Cache::remember($cacheKey, 3600, function() use ($search, $filters, $perPage) {
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
        });
    }

    public function createTask(array $data): Task
    {
        $task = Task::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'tags' => $data['tags'] ?? [],
        ]);

        $this->clearCache();

        return $task;
    }

    public function updateTask(Task $task, array $data): Task
    {
        $task->update([
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

    public function deleteTask(Task $task): void
    {
        $task->delete();
        $this->clearCache();
    }

    public function toggleTaskCompletion(Task $task): Task
    {
        $completed = !$task->completed;

        $task->update(['completed' => $completed]);
        ProcessTaskCompletion::dispatch($task, $completed);

        $this->clearCache();

        return $task->fresh();
    }

    public function forceDeleteTask(Task $task): void
    {
        $task->forceDelete();
        $this->clearCache();
    }

    public function restoreTask(Task $task): void
    {
        $task->restore();
        $this->clearCache();
    }

    private function getCacheKey(?string $search, array $filters, int $perPage): string
    {
        return sprintf(
            'tasks_%s_%s_%d',
            $search ? md5($search) : 'all',
            md5(serialize($filters)),
            $perPage
        );
    }

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
