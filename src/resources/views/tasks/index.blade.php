@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-between align-items-center mb-4">
            <div class="col-md-6">
                <h1>Tasks {{ $showDeleted ? '(Deleted)' : '' }}</h1>
            </div>
            <div class="col-md-6 text-end">
                @if($showDeleted)
                    <a href="{{ route('tasks.index') }}" class="btn btn-secondary me-2">View Active Tasks</a>
                @else
                    <a href="{{ route('tasks.index', ['show_deleted' => true]) }}" class="btn btn-warning me-2">View Deleted Tasks</a>
                    <a href="{{ route('tasks.create') }}" class="btn btn-primary">Create New Task</a>
                @endif
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('tasks.index') }}">
                    <input type="hidden" name="show_deleted" value="{{ $showDeleted ? '1' : '0' }}">

                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Search tasks..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="completed" class="form-select">
                                <option value="">All Status</option>
                                <option value="1" {{ request('completed') == '1' ? 'selected' : '' }}>Completed</option>
                                <option value="0" {{ request('completed') == '0' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="tags" class="form-control" placeholder="Filter by tags..."
                                   value="{{ request('tags') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('tasks.index', ['show_deleted' => $showDeleted]) }}" class="btn btn-outline-secondary w-100">Clear Filters</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tasks List -->
        @if($tasks->count() > 0)
            <div class="row">
                @foreach($tasks as $task)
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title {{ $task->completed ? 'text-decoration-line-through' : '' }}">
                                    {{ $task->title }}
                                    @if($task->trashed())
                                        <span class="badge bg-danger ms-2">Deleted</span>
                                    @endif
                                </h5>
                                <p class="card-text">{{ $task->description }}</p>

                                @if(!empty($task->tags))
                                    <div class="mb-2">
                                        @foreach($task->tags as $tag)
                                            <span class="badge bg-secondary me-1">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="d-flex gap-2 flex-wrap">
                                    @if($showDeleted)
                                        <!-- Actions for deleted tasks -->
                                        <form action="{{ route('tasks.restore', $task->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success">Restore</button>
                                        </form>

                                        <form action="{{ route('tasks.forceDelete', $task->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Permanently delete this task?')">Delete Permanently</button>
                                        </form>
                                    @else
                                        <!-- Actions for active tasks -->
                                        <form action="{{ route('tasks.toggle', $task) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $task->completed ? 'btn-warning' : 'btn-success' }}">
                                                {{ $task->completed ? 'Mark Pending' : 'Mark Complete' }}
                                            </button>
                                        </form>

                                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-primary">Edit</a>

                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Improved Pagination with page numbers -->
            <div class="mt-4">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <!-- Previous Page Link -->
                        @if ($tasks->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">&laquo;</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $tasks->previousPageUrl() }}" rel="prev">&laquo;</a>
                            </li>
                        @endif

                        <!-- Page Numbers -->
                        @php
                            $current = $tasks->currentPage();
                            $last = $tasks->lastPage();
                            $start = max($current - 2, 1);
                            $end = min($current + 2, $last);

                            if ($start > 1) {
                                echo '<li class="page-item"><span class="page-link">...</span></li>';
                            }
                        @endphp

                        @for ($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-link" href="{{ $tasks->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        @if ($end < $last)
                            <li class="page-item"><span class="page-link">...</span></li>
                        @endif

                        <!-- Next Page Link -->
                        @if ($tasks->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $tasks->nextPageUrl() }}" rel="next">&raquo;</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">&raquo;</span>
                            </li>
                        @endif
                    </ul>
                </nav>

                <div class="text-center text-muted mt-2">
                    Showing {{ $tasks->firstItem() }} to {{ $tasks->lastItem() }} of {{ $tasks->total() }} results
                </div>
            </div>
        @else
            <div class="alert alert-info">
                @if($showDeleted)
                    No deleted tasks found.
                @else
                    No tasks found.
                @endif
            </div>
        @endif
    </div>
@endsection
