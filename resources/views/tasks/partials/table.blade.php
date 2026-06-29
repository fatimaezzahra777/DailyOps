@php
    $statusClasses = [
        'todo' => 'tag-chip',
        'in_progress' => 'tag-chip tag-chip-amber',
        'done' => 'tag-chip tag-chip-emerald',
    ];

    $priorityClasses = [
        'low' => 'tag-chip',
        'medium' => 'tag-chip tag-chip-amber',
        'high' => 'tag-chip tag-chip-violet',
    ];

    $statusLabels = [
        'todo' => 'To do',
        'in_progress' => 'In progress',
        'done' => 'Completed',
    ];

    $priorityLabels = [
        'low' => 'low',
        'medium' => 'medium',
        'high' => 'high',
    ];

    $taskBoardColumns = collect([
        [
            'title' => 'To do',
            'status' => 'todo',
            'description' => 'Tasks ready to be started.',
            'laneClass' => 'kanban-lane-pending',
            'badgeClass' => 'kanban-count-pending',
            'cardAccent' => 'project-card-accent-pending',
        ],
        [
            'title' => 'In progress',
            'status' => 'in_progress',
            'description' => 'Tasks currently in progress.',
            'laneClass' => 'kanban-lane-progress',
            'badgeClass' => 'kanban-count-progress',
            'cardAccent' => 'project-card-accent-progress',
        ],
        [
            'title' => 'Completeds',
            'status' => 'done',
            'description' => 'Completed and delivered tasks.',
            'laneClass' => 'kanban-lane-completed',
            'badgeClass' => 'kanban-count-completed',
            'cardAccent' => 'project-card-accent-completed',
        ],
    ]);
@endphp

<div class="space-y-5">
    <div class="kanban-shell custom-scroll overflow-x-auto pb-4" data-task-board>
        <div class="grid min-w-[900px] grid-cols-3 gap-4">
            @foreach ($taskBoardColumns as $column)
                @php
                    $columnTasks = $tasks->getCollection()->where('status', $column['status'])->values();
                @endphp

                <section class="board-column kanban-lane {{ $column['laneClass'] }}">
                    <div class="kanban-lane-head">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="board-column-title">{{ $column['title'] }}</h2>
                                <p class="kanban-lane-description">{{ $column['description'] }}</p>
                            </div>
                            <span class="board-column-count {{ $column['badgeClass'] }}">{{ $columnTasks->count() }}</span>
                        </div>
                    </div>

                    <div class="board-drop-zone space-y-3" data-task-drop-zone data-task-status="{{ $column['status'] }}">
                        @forelse ($columnTasks as $task)
                            @php
                                $assigneeName = $task->assignedUser?->name ?? $task->assigned_to;
                                $canMoveTask = $task->project?->isManagedBy(auth()->user()) ?? false;
                            @endphp

                            <article class="task-card project-card {{ $column['cardAccent'] }} {{ $canMoveTask ? '' : 'cursor-default' }}"
                                data-task-id="{{ $task->id }}"
                                @if ($canMoveTask) draggable="true" data-draggable-task @endif>
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <button type="button" class="task-title text-left hover:text-[var(--accent)]"
                                            data-modal-open="task-details-modal-{{ $task->id }}">
                                            {{ $task->title }}
                                        </button>
                                        <p class="mt-2 text-xs leading-5 text-[var(--muted)]">
                                            {{ \Illuminate\Support\Str::limit($task->description, 80) ?: 'Noe description.' }}
                                        </p>
                                    </div>
                                    <span class="{{ $priorityClasses[$task->priority] ?? 'tag-chip' }}">
                                        {{ $priorityLabels[$task->priority] ?? $task->priority }}
                                    </span>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if ($task->project)
                                        <span class="tag-chip">{{ $task->project->name }}</span>
                                    @endif
                                    <span class="tag-chip">{{ $task->comments->count() }} commentaire{{ $task->comments->count() > 1 ? 's' : '' }}</span>
                                </div>

                                <div class="mt-4 grid gap-2 text-xs text-[var(--muted)]">
                                    <div class="flex items-center justify-between gap-3">
                                        <span>Assignee</span>
                                        <span class="truncate text-[var(--text-strong)]">{{ $assigneeName ?: 'Unassigned' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span>Due date</span>
                                        <span class="text-[var(--text-strong)]">{{ $task->due_date ? \Illuminate\Support\Carbon::parse($task->due_date)->format('d M Y') : 'Not set' }}</span>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="empty-column-card min-h-32">
                                <p>Noe task dans cette colonne.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    <div class="panel-dark overflow-hidden">
    <div class="hidden overflow-x-auto xl:block">
        <table class="w-full">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Due date</th>
                    <th>Assignee</th>
                    <th>Comments</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tasks as $task)
                    @php
                        $assigneeName = $task->assignedUser?->name ?? $task->assigned_to;
                    @endphp
                    <tr class="transition hover:bg-white/5">
                        <td>
                            <div class="min-w-[220px]">
                                <a href="{{ route('tasks.show', $task) }}" class="text-sm font-semibold text-[var(--text-strong)] hover:text-[var(--accent)]">
                                    {{ $task->title }}
                                </a>
                                <p class="mt-1 text-sm text-[var(--muted)]">{{ \Illuminate\Support\Str::limit($task->description, 70) }}</p>
                            </div>
                        </td>
                        <td class="text-sm">{{ $task->project?->name ?? 'No project' }}</td>
                        <td>
                            <span class="{{ $statusClasses[$task->status] ?? 'tag-chip' }}">{{ $statusLabels[$task->status] ?? $task->status }}</span>
                        </td>
                        <td>
                            <span class="{{ $priorityClasses[$task->priority] ?? 'tag-chip' }}">{{ $priorityLabels[$task->priority] ?? $task->priority }}</span>
                        </td>
                        <td class="text-sm">{{ $task->due_date ? \Illuminate\Support\Carbon::parse($task->due_date)->format('d M Y') : 'Not set' }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="mini-avatar !h-8 !w-8 !text-[11px]">{{ strtoupper(substr($assigneeName ?: 'T', 0, 1)) }}</div>
                                <span class="text-sm">{{ $assigneeName ?: 'Unassigned' }}</span>
                            </div>
                        </td>
                        <td class="text-sm">{{ $task->comments->count() }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <button type="button" class="icon-button h-8 w-8 p-0"
                                    data-modal-open="task-details-modal-{{ $task->id }}"
                                    aria-label="View task" title="View task">
                                    <span class="material-symbols-rounded text-[18px]">visibility</span>
                                </button>
                                <button type="button" class="icon-button h-8 w-8 p-0"
                                    data-modal-open="edit-task-modal-{{ $task->id }}"
                                    aria-label="Edit task" title="Edit task">
                                    <span class="material-symbols-rounded text-[18px]">edit</span>
                                </button>
                                <button type="button" class="icon-button h-8 w-8 p-0"
                                    data-modal-open="delete-task-modal-{{ $task->id }}"
                                    aria-label="Delete task" title="Delete task">
                                    <span class="material-symbols-rounded text-[18px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-sm text-[var(--muted)]">Noe task ne correspond aux filtres actuels.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="grid gap-4 p-4 xl:hidden">
        @forelse ($tasks as $task)
            @php
                $assigneeName = $task->assignedUser?->name ?? $task->assigned_to;
            @endphp
            <article class="rounded-2xl border border-[var(--line)] bg-[var(--card)] p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <button type="button" class="text-left text-base font-semibold text-[var(--text-strong)] hover:text-[var(--accent)]"
                            data-modal-open="task-details-modal-{{ $task->id }}">
                            {{ $task->title }}
                        </button>
                        <p class="mt-1 text-sm text-[var(--muted)]">{{ \Illuminate\Support\Str::limit($task->description, 90) }}</p>
                    </div>
                    <span class="{{ $statusClasses[$task->status] ?? 'tag-chip' }}">{{ $statusLabels[$task->status] ?? $task->status }}</span>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="{{ $priorityClasses[$task->priority] ?? 'tag-chip' }}">{{ $priorityLabels[$task->priority] ?? $task->priority }}</span>
                    @if ($task->project)
                        <span class="tag-chip">{{ $task->project->name }}</span>
                    @endif
                    <span class="tag-chip">{{ $task->comments->count() }} commentaire{{ $task->comments->count() > 1 ? 's' : '' }}</span>
                </div>

                <div class="mt-4 grid gap-2 text-sm text-[var(--muted)]">
                    <div class="flex justify-between gap-3">
                        <span>Assignee</span>
                        <span class="text-[var(--text-strong)]">{{ $assigneeName ?: 'Unassigned' }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span>Due date</span>
                        <span class="text-[var(--text-strong)]">{{ $task->due_date ? \Illuminate\Support\Carbon::parse($task->due_date)->format('d M Y') : 'Not set' }}</span>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap justify-end gap-2">
                    <button type="button" class="icon-button h-9 w-9 p-0" data-modal-open="task-details-modal-{{ $task->id }}"
                        aria-label="View task" title="View task">
                        <span class="material-symbols-rounded text-[19px]">visibility</span>
                    </button>
                    <button type="button" class="icon-button h-9 w-9 p-0" data-modal-open="edit-task-modal-{{ $task->id }}"
                        aria-label="Edit task" title="Edit task">
                        <span class="material-symbols-rounded text-[19px]">edit</span>
                    </button>
                    <button type="button" class="icon-button h-9 w-9 p-0" data-modal-open="delete-task-modal-{{ $task->id }}"
                        aria-label="Delete task" title="Delete task">
                        <span class="material-symbols-rounded text-[19px]">delete</span>
                    </button>
                </div>
            </article>
        @empty
            <div class="empty-column-card min-h-40">
                <p>Noe task ne correspond aux filtres actuels.</p>
            </div>
        @endforelse
    </div>
    </div>
</div>
