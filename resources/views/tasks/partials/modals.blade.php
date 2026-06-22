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
                <p class="modal-eyebrow">Créer une tâche</p>
                <h2 class="modal-title">Nouvelle tâche</h2>
                <p class="modal-subtitle">Créez une tâche sans quitter cette page.</p>
            </div>
            <button type="button" class="modal-close" data-modal-close aria-label="Fermer la fenêtre">×</button>
        </div>

        <form action="{{ route('tasks.store') }}" method="POST" class="space-y-5" autocomplete="off" spellcheck="false">
            @csrf
            <input type="text" tabindex="-1" autocomplete="username" class="hidden" aria-hidden="true">
            <input type="password" tabindex="-1" autocomplete="new-password" class="hidden" aria-hidden="true">

            @include('tasks.partials.form', [
                'task' => null,
                'prefix' => 'create-task',
                'errorBag' => 'createTask',
                'useOldValues' => $openModal === 'create-task-modal',
                'disableAutofill' => true,
                'namePrefix' => 'create_',
                'oldKeyPrefix' => 'create_',
            ])

            <div class="modal-actions">
                <button type="submit" class="btn-primary">Créer une tâche</button>
                <button type="button" class="btn-secondary" data-modal-close>Annuler</button>
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
                    <p class="modal-eyebrow">Détails de la tâche</p>
                    <h2 class="modal-title">{{ $task->title }}</h2>
                    <p class="modal-subtitle">Consultez les informations, les notes et les actions de la tâche.</p>
                </div>
                <button type="button" class="modal-close" data-modal-close aria-label="Fermer la fenêtre">×</button>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
                <article class="panel-dark p-5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="{{ $statusClasses[$task->status] ?? 'tag-chip' }}">{{ ['todo' => 'À faire', 'in_progress' => 'En cours', 'done' => 'Terminée'][$task->status] ?? $task->status }}</span>
                        <span class="{{ $priorityClasses[$task->priority] ?? 'tag-chip' }}">Priorité {{ ['low' => 'faible', 'medium' => 'moyenne', 'high' => 'élevée'][$task->priority] ?? $task->priority }}</span>
                        @if ($task->project)
                            <span class="tag-chip">{{ $task->project->name }}</span>
                        @endif
                    </div>

                    <p class="mt-4 text-sm leading-7 text-[var(--text)]">
                        {{ $task->description ?: 'Aucune description n’a encore été ajoutée à cette tâche.' }}
                    </p>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="{{ route('tasks.show', $task) }}" class="btn-secondary">Ouvrir les détails complets</a>
                        <a href="{{ route('projects.show', $task->project) }}" class="btn-secondary">Voir le projet</a>
                    </div>
                </article>

                <aside class="space-y-4">
                    <div class="panel-dark p-5">
                        <h3 class="text-sm font-semibold text-[var(--text-strong)]">Informations sur la tâche</h3>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[var(--muted)]">Responsable</dt>
                                <dd class="text-[var(--text-strong)]">{{ $assigneeName ?: 'Non assignée' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[var(--muted)]">Date d’échéance</dt>
                                <dd class="text-[var(--text-strong)]">{{ $task->due_date ? \Illuminate\Support\Carbon::parse($task->due_date)->format('d M Y') : 'Non définie' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[var(--muted)]">Commentaires</dt>
                                <dd class="text-[var(--text-strong)]">{{ $task->comments->count() }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="icon-button h-10 w-10 p-0" data-modal-switch="{{ $editModalId }}"
                            aria-label="Modifier la tâche" title="Modifier la tâche">
                            <span class="material-symbols-rounded text-[20px]">edit</span>
                        </button>
                        <button type="button" class="icon-button h-10 w-10 p-0" data-modal-switch="{{ $deleteModalId }}"
                            aria-label="Supprimer la tâche" title="Supprimer la tâche">
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
                    <p class="modal-eyebrow">Mettre à jour la tâche</p>
                    <h2 class="modal-title">{{ $task->title }}</h2>
                    <p class="modal-subtitle">Modifiez la tâche directement depuis la liste.</p>
                </div>
                <button type="button" class="modal-close" data-modal-close aria-label="Fermer la fenêtre">×</button>
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
                    <button type="submit" class="btn-primary">Mettre à jour la tâche</button>
                    <button type="button" class="btn-secondary" data-modal-close>Annuler</button>
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
                    <p class="modal-eyebrow text-rose-300">Supprimer la tâche</p>
                    <h2 class="modal-title">{{ $task->title }}</h2>
                    <p class="modal-subtitle">Cette tâche sera définitivement supprimée.</p>
                </div>
                <button type="button" class="modal-close" data-modal-close aria-label="Fermer la fenêtre">×</button>
            </div>

            <div class="rounded-2xl border border-rose-500/15 bg-rose-500/10 p-4 text-sm text-[var(--text)]">
                Vous allez supprimer définitivement cette tâche de la liste.
            </div>

            <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="modal-actions">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-primary">Supprimer la tâche</button>
                <button type="button" class="btn-secondary" data-modal-close>Annuler</button>
            </form>
        </div>
    </div>
@endforeach
