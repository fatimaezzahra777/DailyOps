@php
    $openModal = $openModal ?? session('open_modal');

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
@endphp

<div class="modal-shell {{ $openModal === 'create-task-modal' ? '' : 'hidden' }}" id="create-task-modal"
    data-reset-on-open="true" data-modal tabindex="-1"
    aria-hidden="{{ $openModal === 'create-task-modal' ? 'false' : 'true' }}">
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-panel modal-panel-form">
        <div class="modal-header">
            <div>
                <p class="modal-eyebrow">Create task</p>
                <h2 class="modal-title">New task</h2>
                <p class="modal-subtitle">Create a task without leaving the tasks page.</p>
            </div>
            <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
        </div>

        <form action="{{ route('tasks.store') }}" method="POST" class="space-y-5" autocomplete="off" spellcheck="false">
            @csrf
            <input type="text" tabindex="-1" autocomplete="username" class="hidden" aria-hidden="true">
            <input type="password" tabindex="-1" autocomplete="new-password" class="hidden" aria-hidden="true">

            @include('tasks.partials.form', [
                'prefix' => 'create-task',
                'errorBag' => 'createTask',
                'useOldValues' => $openModal === 'create-task-modal',
                'disableAutofill' => true,
                'namePrefix' => 'create_',
                'oldKeyPrefix' => 'create_',
            ])

            <div class="modal-actions">
                <button type="submit" class="btn-primary">Create task</button>
                <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
            </div>
        </form>
    </div>
</div>

@foreach ($tasks as $task)
    @php
        $detailModalId = "task-details-modal-{$task->id}";
        $editModalId = "edit-task-modal-{$task->id}";
        $deleteModalId = "delete-task-modal-{$task->id}";
        $editBag = "updateTask.{$task->id}";
        $assigneeName = $task->assignedUser?->name ?? $task->assigned_to;
    @endphp

    <div class="modal-shell {{ $openModal === $detailModalId ? '' : 'hidden' }}" id="{{ $detailModalId }}" data-modal tabindex="-1"
        aria-hidden="{{ $openModal === $detailModalId ? 'false' : 'true' }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-panel">
            <div class="modal-header">
                <div>
                    <p class="modal-eyebrow">Task details</p>
                    <h2 class="modal-title">{{ $task->title }}</h2>
                    <p class="modal-subtitle">Quick task overview, notes and actions in one place.</p>
                </div>
                <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
                <article class="panel-dark p-5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="{{ $statusClasses[$task->status] ?? 'tag-chip' }}">{{ str($task->status)->replace('_', ' ')->title() }}</span>
                        <span class="{{ $priorityClasses[$task->priority] ?? 'tag-chip' }}">{{ ucfirst($task->priority) }} priority</span>
                        @if ($task->project)
                            <span class="tag-chip">{{ $task->project->name }}</span>
                        @endif
                    </div>

                    <p class="mt-4 text-sm leading-7 text-[var(--text)]">
                        {{ $task->description ?: 'No description has been added for this task yet.' }}
                    </p>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="{{ route('tasks.show', $task) }}" class="btn-secondary">Open full details</a>
                        <a href="{{ route('projects.show', $task->project) }}" class="btn-secondary">View project</a>
                    </div>
                </article>

                <aside class="space-y-4">
                    <div class="panel-dark p-5">
                        <h3 class="text-sm font-semibold text-[var(--text-strong)]">Task info</h3>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[var(--muted)]">Assignee</dt>
                                <dd class="text-[var(--text-strong)]">{{ $assigneeName ?: 'Unassigned' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[var(--muted)]">Due date</dt>
                                <dd class="text-[var(--text-strong)]">{{ $task->due_date ? \Illuminate\Support\Carbon::parse($task->due_date)->format('d M Y') : 'Not set' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[var(--muted)]">Comments</dt>
                                <dd class="text-[var(--text-strong)]">{{ $task->comments->count() }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="icon-button h-10 w-10 p-0" data-modal-switch="{{ $editModalId }}"
                            aria-label="Edit task" title="Edit task">
                            <span class="material-symbols-rounded text-[20px]">edit</span>
                        </button>
                        <button type="button" class="icon-button h-10 w-10 p-0" data-modal-switch="{{ $deleteModalId }}"
                            aria-label="Delete task" title="Delete task">
                            <span class="material-symbols-rounded text-[20px]">delete</span>
                        </button>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    <div class="modal-shell {{ $openModal === $editModalId ? '' : 'hidden' }}" id="{{ $editModalId }}" data-modal tabindex="-1"
        aria-hidden="{{ $openModal === $editModalId ? 'false' : 'true' }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-panel modal-panel-form">
            <div class="modal-header">
                <div>
                    <p class="modal-eyebrow">Update task</p>
                    <h2 class="modal-title">{{ $task->title }}</h2>
                    <p class="modal-subtitle">Edit the task directly from the tasks list.</p>
                </div>
                <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
            </div>

            <form action="{{ route('tasks.update', $task) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                @include('tasks.partials.form', [
                    'task' => $task,
                    'projects' => $projects,
                    'prefix' => "edit-task-{$task->id}",
                    'errorBag' => $editBag,
                    'useOldValues' => $openModal === $editModalId,
                ])

                <div class="modal-actions">
                    <button type="submit" class="btn-primary">Update task</button>
                    <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-shell {{ $openModal === $deleteModalId ? '' : 'hidden' }}" id="{{ $deleteModalId }}" data-modal tabindex="-1"
        aria-hidden="{{ $openModal === $deleteModalId ? 'false' : 'true' }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-panel modal-panel-compact">
            <div class="modal-header">
                <div>
                    <p class="modal-eyebrow text-rose-300">Delete task</p>
                    <h2 class="modal-title">{{ $task->title }}</h2>
                    <p class="modal-subtitle">This task will be permanently removed.</p>
                </div>
                <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
            </div>

            <div class="rounded-2xl border border-rose-500/15 bg-rose-500/10 p-4 text-sm text-[var(--text)]">
                You are about to delete this task and its workflow history from the current list.
            </div>

            <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="modal-actions">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-primary">Delete task</button>
                <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
            </form>
        </div>
    </div>
@endforeach
