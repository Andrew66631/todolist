<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTaskCompletion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Task $task,
        public bool $completed
    ) {}

    public function handle(): void
    {
        if ($this->completed) {
            $this->task->update([
                'completed_at' => now(),
            ]);

            Log::info("Задача {$this->task->id} перенесена в завершенные " . now());
        }
    }
}
