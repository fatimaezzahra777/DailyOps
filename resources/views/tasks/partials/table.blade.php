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
                            <span class="{{ $statusClasses[$task->status] ?? 'tag-chip' }}">{{ ['todo' => 'À faire', 'in_progress' => 'En cours', 'done' => 'Terminée'][$task->status] ?? $task->status }}</span>
                        </td>
                        <td>
                            <span class="{{ $priorityClasses[$task->priority] ?? 'tag-chip' }}">{{ ucfirst($task->priority) }}</span>
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
                    <span class="{{ $statusClasses[$task->status] ?? 'tag-chip' }}">{{ ['todo' => 'À faire', 'in_progress' => 'En cours', 'done' => 'Terminée'][$task->status] ?? $task->status }}</span>
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
