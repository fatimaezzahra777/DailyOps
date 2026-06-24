@extends('layouts.app')

@section('content')
    @php
        $statusClasses = [
            'pending' => 'tag-chip tag-chip-violet',
            'in_progress' => 'tag-chip tag-chip-amber',
            'testing' => 'tag-chip tag-chip-indigo',
            'completed' => 'tag-chip tag-chip-emerald',
        ];
        $canManageProject = $project->isManagedBy(auth()->user());
        $openModal = session('open_modal');
        $tasks = $project->tasks;
        $customTaskColumns = $project->taskColumns;
        $projects = collect([$project]);
        $acceptedCollaborators = $project->collaborators
            ->merge(
                $project->invitations
                    ->where('status', \App\Models\ProjectInvitation::STATUS_ACCEPTED)
                    ->pluck('user')
                    ->filter()
            )
            ->unique('id')
            ->values();
        $taskColumns = [
            'todo' => [
                'title' => 'To do',
                'empty' => 'No tasks to start.',
                'description' => 'Ideas, specs, and tasks ready to pick up.',
                'dot' => 'bg-[#c50064]',
                'laneClass' => 'kanban-lane-pending',
                'badgeClass' => 'kanban-count-pending',
                'cardAccent' => 'project-card-accent-pending',
            ],
            'in_progress' => [
                'title' => 'In progress',
                'empty' => 'No active tasks.',
                'description' => 'Work currently moving through the project.',
                'dot' => 'bg-[#f59e0b]',
                'laneClass' => 'kanban-lane-progress',
                'badgeClass' => 'kanban-count-progress',
                'cardAccent' => 'project-card-accent-progress',
            ],
            'done' => [
                'title' => 'Done',
                'empty' => 'No completed tasks.',
                'description' => 'Finished tasks and delivered work.',
                'dot' => 'bg-[#00a86b]',
                'laneClass' => 'kanban-lane-completed',
                'badgeClass' => 'kanban-count-completed',
                'cardAccent' => 'project-card-accent-completed',
            ],
        ];
        $taskStatusClasses = [
            'todo' => 'tag-chip',
            'in_progress' => 'tag-chip tag-chip-amber',
            'done' => 'tag-chip tag-chip-emerald',
        ];
    @endphp

    <section class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--muted)]">Project workspace</p>
                <div class="mt-2 flex items-center gap-3">
                    @if ($project->projectLogoUrl())
                        <img src="{{ $project->projectLogoUrl() }}" alt="Logo de {{ $project->name }}"
                            class="project-logo-circle project-logo-circle-large">
                    @endif
                    <h1 class="text-3xl font-semibold">{{ $project->name }}</h1>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="{{ $statusClasses[$project->status] ?? 'tag-chip' }}">
                        {{ \App\Models\Project::statusLabel($project->status) }}
                    </span>
                    @if ($project->manager)
                        <span class="tag-chip tag-chip-violet">Manager: {{ $project->manager->name }}</span>
                    @endif
                    @unless ($canManageProject)
                        <span class="tag-chip tag-chip-emerald">Collaborator</span>
                    @endunless
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if ($canManageProject)
                    <button type="button" class="icon-button h-11 w-11 px-0" data-modal-open="invite-collaborator-modal"
                        aria-label="Add collaborator" title="Add collaborator">
                        <span class="material-symbols-rounded text-[24px]">person_add</span>
                    </button>
                    <button type="button" class="icon-button h-11 w-11 px-0" data-modal-open="create-task-modal"
                        aria-label="Add task" title="Add task">
                        <span class="material-symbols-rounded text-[24px]">add_task</span>
                    </button>
                    <a href="{{ route('projects.edit', $project) }}" class="btn-secondary">Edit project</a>
                @endif
                <a href="{{ route('projects.index') }}" class="btn-secondary">Back to board</a>
            </div>
        </div>

        <section class="panel-dark p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--muted)]">Project tasks</p>
                    <h2 class="mt-2 text-2xl font-semibold">Task board</h2>
                </div>
                @if ($canManageProject)
                    <div class="flex items-center gap-2">
                        <button type="button" class="icon-button h-10 w-10 px-0" data-modal-open="invite-collaborator-modal"
                            aria-label="Add collaborator" title="Add collaborator">
                            <span class="material-symbols-rounded text-[22px]">person_add</span>
                        </button>
                        <button type="button" class="icon-button h-10 w-10 px-0" data-modal-open="create-task-modal"
                            aria-label="Add task" title="Add task">
                            <span class="material-symbols-rounded text-[22px]">add_task</span>
                        </button>
                    </div>
                @endif
            </div>

            <div class="kanban-shell custom-scroll mt-6 overflow-x-auto pb-4" data-task-board>
                <div class="board-grid">
                @foreach ($taskColumns as $status => $taskColumn)
                    @php
                        $columnTasks = $tasks->whereNull('task_column_id')->where('status', $status)->values();
                    @endphp
                    <section class="board-column kanban-lane {{ $taskColumn['laneClass'] }}">
                        <div class="kanban-lane-head">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-1 h-2.5 w-2.5 rounded-full {{ $taskColumn['dot'] }}"></span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="board-column-title">{{ $taskColumn['title'] }}</h3>
                                        <span class="board-column-count {{ $taskColumn['badgeClass'] }}">{{ $columnTasks->count() }}</span>
                                    </div>
                                    <p class="kanban-lane-description">{{ $taskColumn['description'] }}</p>
                                </div>
                            </div>
                            @if ($canManageProject)
                                <button type="button" class="icon-button h-7 w-7 p-0" aria-label="Add task"
                                    data-modal-open="create-task-modal"
                                    data-create-task-status="{{ $status }}"
                                    data-create-task-column-id="">
                                    <span class="material-symbols-rounded text-[18px]">add</span>
                                </button>
                            @endif
                        </div>

                        <div class="board-drop-zone space-y-3" data-task-drop-zone data-task-list data-task-status="{{ $status }}">
                            @forelse ($columnTasks as $task)
                                @php
                                    $assigneeName = $task->assignedUser?->name ?? $task->assigned_to;
                                    $assigneeInitial = $assigneeName ? strtoupper(substr($assigneeName, 0, 1)) : null;
                                @endphp
                                <article class="task-card project-card {{ $taskColumn['cardAccent'] }} {{ $loop->index >= 15 ? 'hidden' : '' }}"
                                    @if ($canManageProject) draggable="true" data-draggable-task @endif
                                    @if ($loop->index >= 15) data-task-overflow @endif
                                    data-task-id="{{ $task->id }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <a href="{{ route('tasks.show', $task) }}" class="task-title text-left hover:text-[#c50064]">{{ $task->title }}</a>
                                        <span class="{{ $taskStatusClasses[$task->status] ?? 'tag-chip' }}">
                                            {{ str($task->status)->replace('_', ' ')->title() }}
                                        </span>
                                    </div>
                                    @if ($task->description)
                                        <p class="mt-3 text-xs leading-5 text-[var(--muted)]">{{ \Illuminate\Support\Str::limit($task->description, 90) }}</p>
                                    @endif
                                    <div class="mt-4 flex flex-wrap items-center gap-2">
                                        <span class="tag-chip">{{ ucfirst($task->priority) }}</span>
                                        <span class="tag-chip">{{ $assigneeName ?: 'Unassigned' }}</span>
                                        @if ($task->due_date)
                                            <span class="tag-chip">{{ $task->due_date->format('d M') }}</span>
                                        @endif
                                    </div>

                                    <div class="mt-4 flex items-center justify-between gap-3 border-t border-[var(--line)] pt-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('tasks.show', $task) }}" class="icon-button h-8 w-8 p-0" aria-label="View task" title="View task">
                                                <span class="material-symbols-rounded text-[18px]">visibility</span>
                                            </a>
                                            @if ($canManageProject)
                                                <button type="button" class="icon-button h-8 w-8 p-0" data-modal-open="edit-task-modal-{{ $task->id }}"
                                                    aria-label="Edit task" title="Edit task">
                                                    <span class="material-symbols-rounded text-[18px]">edit</span>
                                                </button>
                                                <button type="button" class="icon-button h-8 w-8 p-0" data-modal-open="delete-task-modal-{{ $task->id }}"
                                                    aria-label="Delete task" title="Delete task">
                                                    <span class="material-symbols-rounded text-[18px]">delete</span>
                                                </button>
                                            @endif
                                        </div>
                                        @if ($assigneeInitial)
                                            <span class="mini-avatar" title="{{ $assigneeName }}">{{ $assigneeInitial }}</span>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <p class="empty-column-card">
                                    {{ $taskColumn['empty'] }}
                                </p>
                            @endforelse
                        </div>

                        @if ($columnTasks->count() > 15)
                            <button type="button" class="kanban-show-more" data-task-list-toggle aria-expanded="false">
                                <span data-task-list-toggle-label>Voir {{ $columnTasks->count() - 15 }} tâches de plus</span>
                                <i class="ti ti-chevron-down" data-task-list-toggle-icon></i>
                            </button>
                        @endif
                    </section>
                @endforeach

                @foreach ($customTaskColumns as $taskColumn)
                    @php
                        $columnTasks = $tasks->where('task_column_id', $taskColumn->id)->values();
                    @endphp
                    <section class="board-column kanban-lane kanban-lane-empty">
                        <div class="kanban-lane-head">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-1 h-2.5 w-2.5 rounded-full bg-sky-500"></span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="board-column-title">{{ $taskColumn->name }}</h3>
                                        <span class="board-column-count kanban-count-empty">{{ $columnTasks->count() }}</span>
                                    </div>
                                    <p class="kanban-lane-description">Custom task workflow column.</p>
                                </div>
                            </div>
                            @if ($canManageProject)
                                <div class="flex items-center gap-1">
                                    <button type="button" class="icon-button h-7 w-7 p-0" aria-label="Add task"
                                        data-modal-open="create-task-modal"
                                        data-create-task-status="todo"
                                        data-create-task-column-id="{{ $taskColumn->id }}">
                                        <span class="material-symbols-rounded text-[18px]">add</span>
                                    </button>
                                    <button type="button" class="icon-button h-7 w-7 p-0" aria-label="Edit column"
                                        data-modal-open="edit-task-column-modal-{{ $taskColumn->id }}">
                                        <span class="material-symbols-rounded text-[17px]">edit</span>
                                    </button>
                                    <button type="button" class="icon-button h-7 w-7 p-0" aria-label="Delete column"
                                        data-modal-open="delete-task-column-modal-{{ $taskColumn->id }}">
                                        <span class="material-symbols-rounded text-[17px]">delete</span>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="board-drop-zone space-y-3" data-task-drop-zone data-task-list data-task-column-id="{{ $taskColumn->id }}">
                            @forelse ($columnTasks as $task)
                                @php
                                    $assigneeName = $task->assignedUser?->name ?? $task->assigned_to;
                                    $assigneeInitial = $assigneeName ? strtoupper(substr($assigneeName, 0, 1)) : null;
                                @endphp
                                <article class="task-card project-card project-card-accent-empty {{ $loop->index >= 15 ? 'hidden' : '' }}"
                                    @if ($canManageProject) draggable="true" data-draggable-task @endif
                                    @if ($loop->index >= 15) data-task-overflow @endif
                                    data-task-id="{{ $task->id }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <a href="{{ route('tasks.show', $task) }}" class="task-title text-left hover:text-[#c50064]">{{ $task->title }}</a>
                                        <span class="{{ $taskStatusClasses[$task->status] ?? 'tag-chip' }}">
                                            {{ str($task->status)->replace('_', ' ')->title() }}
                                        </span>
                                    </div>
                                    @if ($task->description)
                                        <p class="mt-3 text-xs leading-5 text-[var(--muted)]">{{ \Illuminate\Support\Str::limit($task->description, 90) }}</p>
                                    @endif
                                    <div class="mt-4 flex flex-wrap items-center gap-2">
                                        <span class="tag-chip">{{ ucfirst($task->priority) }}</span>
                                        <span class="tag-chip">{{ $assigneeName ?: 'Unassigned' }}</span>
                                        @if ($task->due_date)
                                            <span class="tag-chip">{{ $task->due_date->format('d M') }}</span>
                                        @endif
                                    </div>

                                    <div class="mt-4 flex items-center justify-between gap-3 border-t border-[var(--line)] pt-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('tasks.show', $task) }}" class="icon-button h-8 w-8 p-0" aria-label="View task" title="View task">
                                                <span class="material-symbols-rounded text-[18px]">visibility</span>
                                            </a>
                                            @if ($canManageProject)
                                                <button type="button" class="icon-button h-8 w-8 p-0" data-modal-open="edit-task-modal-{{ $task->id }}"
                                                    aria-label="Edit task" title="Edit task">
                                                    <span class="material-symbols-rounded text-[18px]">edit</span>
                                                </button>
                                                <button type="button" class="icon-button h-8 w-8 p-0" data-modal-open="delete-task-modal-{{ $task->id }}"
                                                    aria-label="Delete task" title="Delete task">
                                                    <span class="material-symbols-rounded text-[18px]">delete</span>
                                                </button>
                                            @endif
                                        </div>
                                        @if ($assigneeInitial)
                                            <span class="mini-avatar" title="{{ $assigneeName }}">{{ $assigneeInitial }}</span>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <p class="empty-column-card">No tasks in this column.</p>
                            @endforelse
                        </div>

                        @if ($columnTasks->count() > 15)
                            <button type="button" class="kanban-show-more" data-task-list-toggle aria-expanded="false">
                                <span data-task-list-toggle-label>Voir {{ $columnTasks->count() - 15 }} tâches de plus</span>
                                <i class="ti ti-chevron-down" data-task-list-toggle-icon></i>
                            </button>
                        @endif
                    </section>
                @endforeach

                @if ($canManageProject)
                    <section class="board-column kanban-lane kanban-lane-empty">
                        <div class="kanban-lane-head">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-1 h-2.5 w-2.5 rounded-full bg-zinc-400"></span>
                                <div class="min-w-0">
                                    <h3 class="board-column-title">New column</h3>
                                    <p class="kanban-lane-description">Extend this project task workflow.</p>
                                </div>
                            </div>
                            <button type="button" class="icon-button h-7 w-7 p-0" aria-label="Add column"
                                data-modal-open="create-task-column-modal">
                                <span class="material-symbols-rounded text-[18px]">add</span>
                            </button>
                        </div>

                        <button type="button" class="empty-column-card w-full" data-modal-open="create-task-column-modal">
                            <p>Click to add a new task column</p>
                        </button>
                    </section>
                @endif
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
            <article class="panel-dark p-6">
                <h2 class="text-lg font-semibold">Project information</h2>
                <p class="mt-4 text-sm leading-7 text-[var(--text)]">
                    {{ $project->description ?: 'No description has been added for this project yet.' }}
                </p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    <div class="rounded-xl border border-[var(--line)] bg-[var(--card-soft)] p-4">
                        <p class="text-xs text-[var(--muted)]">Entreprise</p>
                        <div class="mt-3 flex items-center gap-3">
                            @if ($project->companyLogo())
                                <span class="project-company-circle project-company-circle-large">
                                    <img src="{{ asset($project->companyLogo()) }}" alt="{{ $project->companyLabel() }}">
                                </span>
                            @endif
                            <p class="text-sm font-semibold text-[var(--text-strong)]">
                                {{ $project->companyLabel() ?? 'Non définie' }}
                            </p>
                        </div>
                    </div>
                    <div class="rounded-xl border border-[var(--line)] bg-[var(--card-soft)] p-4">
                        <p class="text-xs text-[var(--muted)]">Email client</p>
                        <p class="mt-1 break-words text-sm font-semibold text-[var(--text-strong)]">{{ $project->client_email ?: 'Non défini' }}</p>
                    </div>
                    <div class="rounded-xl border border-[var(--line)] bg-[var(--card-soft)] p-4">
                        <p class="text-xs text-[var(--muted)]">Start date</p>
                        <p class="mt-1 text-sm font-semibold text-[var(--text-strong)]">{{ $project->start_date?->format('d M Y') ?? 'Not set' }}</p>
                    </div>
                    <div class="rounded-xl border border-[var(--line)] bg-[var(--card-soft)] p-4">
                        <p class="text-xs text-[var(--muted)]">End date</p>
                        <p class="mt-1 text-sm font-semibold text-[var(--text-strong)]">{{ $project->end_date?->format('d M Y') ?? 'Not set' }}</p>
                    </div>
                    <div class="rounded-xl border border-[var(--line)] bg-[var(--card-soft)] p-4">
                        <p class="text-xs text-[var(--muted)]">Created at</p>
                        <p class="mt-1 text-sm font-semibold text-[var(--text-strong)]">{{ $project->created_at?->format('d M Y') ?? 'Unknown' }}</p>
                    </div>
                </div>
            </article>

            <article class="panel-dark p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold">Collaborators</h2>
                        <p class="mt-1 text-sm text-[var(--muted)]">{{ $acceptedCollaborators->count() }} accepted</p>
                    </div>
                    @if ($canManageProject)
                        <button type="button" class="icon-button h-10 w-10 px-0" data-modal-open="invite-collaborator-modal"
                            aria-label="Add collaborator" title="Add collaborator">
                            <span class="material-symbols-rounded text-[22px]">person_add</span>
                        </button>
                    @endif
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($acceptedCollaborators as $collaborator)
                        <div class="flex items-center justify-between gap-3 rounded-md border border-[var(--line)] p-3">
                            <div>
                                <p class="text-sm font-medium text-[var(--text-strong)]">{{ $collaborator->name }}</p>
                                <p class="text-xs text-[var(--muted)]">{{ $collaborator->email }}</p>
                            </div>
                            <span class="tag-chip tag-chip-emerald">Accepted</span>
                        </div>
                    @empty
                        <p class="rounded-md border border-dashed border-[var(--line)] p-4 text-sm text-[var(--muted)]">
                            No collaborators yet.
                        </p>
                    @endforelse
                </div>
            </article>
        </div>
    </section>

    @if ($canManageProject)
        <div class="modal-shell {{ $openModal === 'create-task-column-modal' ? '' : 'hidden' }}" id="create-task-column-modal"
            data-reset-on-open="true" data-modal tabindex="-1"
            aria-hidden="{{ $openModal === 'create-task-column-modal' ? 'false' : 'true' }}">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-panel modal-panel-compact">
                <div class="modal-header">
                    <div>
                        <p class="modal-eyebrow">Task column</p>
                        <h2 class="modal-title">New column</h2>
                        <p class="modal-subtitle">Add a custom lane to this project task board.</p>
                    </div>
                    <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
                </div>

                <form action="{{ route('task-columns.store', $project) }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="create-task-column-name" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Name</label>
                        <input id="create-task-column-name" name="name" type="text" class="w-full px-4 py-3"
                            data-field-default="" placeholder="Review, QA, Blocked..." required>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Add column</button>
                        <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        @foreach ($customTaskColumns as $taskColumn)
            <div class="modal-shell {{ $openModal === "edit-task-column-modal-{$taskColumn->id}" ? '' : 'hidden' }}"
                id="edit-task-column-modal-{{ $taskColumn->id }}" data-modal tabindex="-1"
                aria-hidden="{{ $openModal === "edit-task-column-modal-{$taskColumn->id}" ? 'false' : 'true' }}">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-panel modal-panel-compact">
                    <div class="modal-header">
                        <div>
                            <p class="modal-eyebrow">Task column</p>
                            <h2 class="modal-title">Rename column</h2>
                        </div>
                        <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
                    </div>

                    <form action="{{ route('task-columns.update', $taskColumn) }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="edit-task-column-name-{{ $taskColumn->id }}" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Name</label>
                            <input id="edit-task-column-name-{{ $taskColumn->id }}" name="name" type="text" class="w-full px-4 py-3"
                                value="{{ $taskColumn->name }}" required>
                        </div>

                        <div class="modal-actions">
                            <button type="submit" class="btn-primary">Update column</button>
                            <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal-shell {{ $openModal === "delete-task-column-modal-{$taskColumn->id}" ? '' : 'hidden' }}"
                id="delete-task-column-modal-{{ $taskColumn->id }}" data-modal tabindex="-1"
                aria-hidden="{{ $openModal === "delete-task-column-modal-{$taskColumn->id}" ? 'false' : 'true' }}">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-panel modal-panel-compact">
                    <div class="modal-header">
                        <div>
                            <p class="modal-eyebrow text-red-600">Delete column</p>
                            <h2 class="modal-title">{{ $taskColumn->name }}</h2>
                            <p class="modal-subtitle">Tasks in this column will return to To do.</p>
                        </div>
                        <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
                    </div>

                    <form action="{{ route('task-columns.destroy', $taskColumn) }}" method="POST" class="modal-actions">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-primary">Delete column</button>
                        <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                    </form>
                </div>
            </div>
        @endforeach

        <div class="modal-shell {{ $openModal === 'invite-collaborator-modal' ? '' : 'hidden' }}" id="invite-collaborator-modal"
            data-reset-on-open="true" data-modal tabindex="-1"
            aria-hidden="{{ $openModal === 'invite-collaborator-modal' ? 'false' : 'true' }}">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-panel modal-panel-compact">
                <div class="modal-header">
                    <div>
                        <p class="modal-eyebrow">Collaborator</p>
                        <h2 class="modal-title">Invite by email</h2>
                        <p class="modal-subtitle">The user joins this project only after accepting the email invitation.</p>
                    </div>
                    <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
                </div>

                <form action="{{ route('project-invitations.store', $project) }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="collaborator-email" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Email</label>
                        <input id="collaborator-email" name="email" type="email" class="w-full px-4 py-3"
                            value="{{ $openModal === 'invite-collaborator-modal' ? old('email') : '' }}"
                            data-field-default="" placeholder="member@example.com" required>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Send invitation</button>
                        <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                    </div>
                </form>

                <div class="mt-6 space-y-3">
                    <h3 class="text-sm font-semibold text-[var(--text-strong)]">Invitations</h3>
                    @forelse ($project->invitations as $invitation)
                        <div class="rounded-md border border-[var(--line)] p-3">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm text-[var(--text-strong)]">{{ $invitation->email }}</span>
                                <span class="tag-chip {{ $invitation->status === 'accepted' ? 'tag-chip-emerald' : ($invitation->status === 'pending' ? 'tag-chip-amber' : '') }}">
                                    {{ ucfirst($invitation->status) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[var(--muted)]">No invitations sent yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        @include('tasks.partials.modals', [
            'tasks' => $tasks,
            'projects' => $projects,
            'openModal' => $openModal,
        ])
    @endif
@endsection
