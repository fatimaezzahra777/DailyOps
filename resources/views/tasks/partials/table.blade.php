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
        'todo' => 'À faire',
        'in_progress' => 'En cours',
        'done' => 'Terminée',
    ];

    $priorityLabels = [
        'low' => 'faible',
        'medium' => 'moyenne',
        'high' => 'élevée',
    ];

    $taskBoardColumns = collect([
        [
            'title' => 'À faire',
            'status' => 'todo',
            'description' => 'Tâches prêtes à être démarrées.',
            'laneClass' => 'kanban-lane-pending',
            'badgeClass' => 'kanban-count-pending',
            'cardAccent' => 'project-card-accent-pending',
        ],
        [
            'title' => 'En cours',
            'status' => 'in_progress',
            'description' => 'Tâches en cours de réalisation.',
            'laneClass' => 'kanban-lane-progress',
            'badgeClass' => 'kanban-count-progress',
            'cardAccent' => 'project-card-accent-progress',
        ],
        [
            'title' => 'Terminées',
            'status' => 'done',
            'description' => 'Tâches finalisées et livrées.',
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
                                            {{ \Illuminate\Support\Str::limit($task->description, 80) ?: 'Aucune description.' }}
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
                                        <span>Responsable</span>
                                        <span class="truncate text-[var(--text-strong)]">{{ $assigneeName ?: 'Non assignée' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span>Échéance</span>
                                        <span class="text-[var(--text-strong)]">{{ $task->due_date ? \Illuminate\Support\Carbon::parse($task->due_date)->format('d M Y') : 'Non définie' }}</span>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="empty-column-card min-h-32">
                                <p>Aucune tâche dans cette colonne.</p>
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
                    <th>Tâche</th>
                    <th>Projet</th>
                    <th>Statut</th>
                    <th>Priorité</th>
                    <th>Date d’échéance</th>
                    <th>Responsable</th>
                    <th>Commentaires</th>
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
                        <td class="text-sm">{{ $task->project?->name ?? 'Aucun projet' }}</td>
                        <td>
                            <span class="{{ $statusClasses[$task->status] ?? 'tag-chip' }}">{{ $statusLabels[$task->status] ?? $task->status }}</span>
                        </td>
                        <td>
                            <span class="{{ $priorityClasses[$task->priority] ?? 'tag-chip' }}">{{ $priorityLabels[$task->priority] ?? $task->priority }}</span>
                        </td>
                        <td class="text-sm">{{ $task->due_date ? \Illuminate\Support\Carbon::parse($task->due_date)->format('d M Y') : 'Non définie' }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="mini-avatar !h-8 !w-8 !text-[11px]">{{ strtoupper(substr($assigneeName ?: 'T', 0, 1)) }}</div>
                                <span class="text-sm">{{ $assigneeName ?: 'Non assignée' }}</span>
                            </div>
                        </td>
                        <td class="text-sm">{{ $task->comments->count() }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <button type="button" class="icon-button h-8 w-8 p-0"
                                    data-modal-open="task-details-modal-{{ $task->id }}"
                                    aria-label="Voir la tâche" title="Voir la tâche">
                                    <span class="material-symbols-rounded text-[18px]">visibility</span>
                                </button>
                                <button type="button" class="icon-button h-8 w-8 p-0"
                                    data-modal-open="edit-task-modal-{{ $task->id }}"
                                    aria-label="Modifier la tâche" title="Modifier la tâche">
                                    <span class="material-symbols-rounded text-[18px]">edit</span>
                                </button>
                                <button type="button" class="icon-button h-8 w-8 p-0"
                                    data-modal-open="delete-task-modal-{{ $task->id }}"
                                    aria-label="Supprimer la tâche" title="Supprimer la tâche">
                                    <span class="material-symbols-rounded text-[18px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-sm text-[var(--muted)]">Aucune tâche ne correspond aux filtres actuels.</td>
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
                        <span>Responsable</span>
                        <span class="text-[var(--text-strong)]">{{ $assigneeName ?: 'Non assignée' }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span>Date d’échéance</span>
                        <span class="text-[var(--text-strong)]">{{ $task->due_date ? \Illuminate\Support\Carbon::parse($task->due_date)->format('d M Y') : 'Non définie' }}</span>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap justify-end gap-2">
                    <button type="button" class="icon-button h-9 w-9 p-0" data-modal-open="task-details-modal-{{ $task->id }}"
                        aria-label="Voir la tâche" title="Voir la tâche">
                        <span class="material-symbols-rounded text-[19px]">visibility</span>
                    </button>
                    <button type="button" class="icon-button h-9 w-9 p-0" data-modal-open="edit-task-modal-{{ $task->id }}"
                        aria-label="Modifier la tâche" title="Modifier la tâche">
                        <span class="material-symbols-rounded text-[19px]">edit</span>
                    </button>
                    <button type="button" class="icon-button h-9 w-9 p-0" data-modal-open="delete-task-modal-{{ $task->id }}"
                        aria-label="Supprimer la tâche" title="Supprimer la tâche">
                        <span class="material-symbols-rounded text-[19px]">delete</span>
                    </button>
                </div>
            </article>
        @empty
            <div class="empty-column-card min-h-40">
                <p>Aucune tâche ne correspond aux filtres actuels.</p>
            </div>
        @endforelse
    </div>
    </div>
</div>
