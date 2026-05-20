@extends('layouts.app')

@section('content')
    @php
        $stats = [
            ['label' => 'Total tasks', 'value' => $tasks->total(), 'meta' => 'Across all projects'],
            ['label' => 'In progress', 'value' => $tasks->getCollection()->where('status', 'in_progress')->count(), 'meta' => 'Current page'],
            ['label' => 'Done', 'value' => $tasks->getCollection()->where('status', 'done')->count(), 'meta' => 'Current page'],
            ['label' => 'High priority', 'value' => $tasks->getCollection()->where('priority', 'high')->count(), 'meta' => 'Needs attention'],
        ];
    @endphp

    <section class="space-y-6" data-task-search>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--muted)]">Task management</p>
                <h1 class="mt-2 text-3xl font-semibold">Tasks</h1>
                <p class="mt-2 text-sm text-[var(--muted)]">Track execution, priorities, deadlines and comments in the same workspace.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('projects.index') }}" class="btn-secondary">Back to projects</a>
                <button type="button" class="btn-primary" data-modal-open="create-task-modal">+ Add task</button>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-4">
            @foreach ($stats as $index => $stat)
                <article class="metric-card {{ $index === 0 ? 'metric-card-featured' : '' }}">
                    <p class="metric-label">{{ $stat['label'] }}</p>
                    <div class="mt-4 flex items-end justify-between gap-3">
                        <p class="metric-value">{{ $stat['value'] }}</p>
                    </div>
                    <p class="mt-2 text-xs text-[var(--muted)]">{{ $stat['meta'] }}</p>
                </article>
            @endforeach
        </div>

        <div class="panel-dark p-5">
            <form id="task-filter-form" class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="grid flex-1 gap-4 md:grid-cols-2 xl:grid-cols-[minmax(260px,1.4fr)_repeat(3,minmax(170px,0.6fr))]">
                    <div>
                        <label for="task-search-input" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Search</label>
                        <input type="text" id="task-search-input" name="search" value="{{ request('search') }}"
                            placeholder="Search tasks, assignees, notes..." class="w-full px-4 py-3">
                    </div>

                    <div>
                        <label for="task-project-filter" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Project</label>
                        <select id="task-project-filter" name="project_id" class="w-full px-4 py-3">
                            <option value="">All projects</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected(request('project_id') == $project->id)>{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="task-status-filter" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Status</label>
                        <select id="task-status-filter" name="status" class="w-full px-4 py-3">
                            <option value="">All status</option>
                            <option value="todo" @selected(request('status') === 'todo')>Todo</option>
                            <option value="in_progress" @selected(request('status') === 'in_progress')>In progress</option>
                            <option value="done" @selected(request('status') === 'done')>Done</option>
                        </select>
                    </div>

                    <div>
                        <label for="task-priority-filter" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Priority</label>
                        <select id="task-priority-filter" name="priority" class="w-full px-4 py-3">
                            <option value="">All priority</option>
                            <option value="low" @selected(request('priority') === 'low')>Low</option>
                            <option value="medium" @selected(request('priority') === 'medium')>Medium</option>
                            <option value="high" @selected(request('priority') === 'high')>High</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-end gap-3">
                    <button type="submit" class="btn-primary">Apply filters</button>
                    <a href="{{ route('tasks.index') }}" class="btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div id="tasks-container">
            @include('tasks.partials.results', ['openModal' => session('open_modal')])
        </div>

        <div id="tasks-pagination" class="pt-2">
            @include('tasks.partials.pagination')
        </div>
    </section>
@endsection
