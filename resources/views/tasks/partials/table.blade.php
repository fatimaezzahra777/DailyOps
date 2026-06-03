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
@endphp

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
                            <span class="{{ $statusClasses[$task->status] ?? 'tag-chip' }}">{{ str($task->status)->replace('_', ' ')->title() }}</span>
                        </td>
                        <td>
                            <span class="{{ $priorityClasses[$task->priority] ?? 'tag-chip' }}">{{ ucfirst($task->priority) }}</span>
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
                        <td colspan="8" class="py-10 text-center text-sm text-[var(--muted)]">No tasks found for the current filters.</td>
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
                    <span class="{{ $statusClasses[$task->status] ?? 'tag-chip' }}">{{ str($task->status)->replace('_', ' ')->title() }}</span>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="{{ $priorityClasses[$task->priority] ?? 'tag-chip' }}">{{ ucfirst($task->priority) }}</span>
                    @if ($task->project)
                        <span class="tag-chip">{{ $task->project->name }}</span>
                    @endif
                    <span class="tag-chip">{{ $task->comments->count() }} comments</span>
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
                <p>No tasks found for the current filters.</p>
            </div>
        @endforelse
    </div>
</div>
