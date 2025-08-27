<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    public function getById(int $id): ?Task;
    public function getByUserWithFilters(int $userId, ?string $search = null, array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): Task;
    public function update(Task $task, array $data): bool;
    public function delete(Task $task): bool;
    public function forceDelete(Task $task): bool;
    public function restore(Task $task): bool;
    public function getDeletedPaginated(?string $search = null, array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function getPaginated(?string $search = null, array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function all(): Collection;
}
