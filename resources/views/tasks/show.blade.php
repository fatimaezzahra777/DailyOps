@extends('layouts.app')

@section('content')
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

        $assigneeName = $task->assignedUser?->name ?? $task->assigned_to;
        $canManageTask = $task->project?->isManagedBy(auth()->user()) ?? false;
    @endphp

    <section class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--muted)]">Détails de la tâche</p>
                <h1 class="mt-2 text-3xl font-semibold">{{ $task->title }}</h1>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="{{ $statusClasses[$task->status] ?? 'tag-chip' }}">{{ ['todo' => 'À faire', 'in_progress' => 'En cours', 'done' => 'Terminée'][$task->status] ?? $task->status }}</span>
                    <span class="{{ $priorityClasses[$task->priority] ?? 'tag-chip' }}">Priorité {{ ['low' => 'faible', 'medium' => 'moyenne', 'high' => 'élevée'][$task->priority] ?? $task->priority }}</span>
                    @if ($assigneeName)
                        <span class="tag-chip">{{ $assigneeName }}</span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                @if ($canManageTask)
                    <a href="{{ route('tasks.edit', $task) }}" class="btn-primary">Modifier la tâche</a>
                @endif
                <a href="{{ route('tasks.index') }}" class="btn-secondary">Retour aux tâches</a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
            <div class="space-y-6">
                <article class="panel-dark p-6">
                    <h2 class="text-lg font-semibold">Vue d’ensemble</h2>
                    <p class="mt-4 text-sm leading-7 text-[var(--text)]">
                        {{ $task->description ?: 'Aucune description n’a encore été ajoutée à cette tâche.' }}
                    </p>
                </article>

                <section class="panel-dark p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold">Commentaires</h2>
                            <p class="mt-1 text-sm text-[var(--muted)]">Keep the context of the task in one place.</p>
                        </div>
                        <span class="tag-chip">{{ $task->comments->count() }} comments</span>
                    </div>

                    <form action="{{ route('comments.store') }}" method="POST" class="mt-6 space-y-4">
                        @csrf
                        <input type="hidden" name="task_id" value="{{ $task->id }}">

                        <div class="grid gap-4 md:grid-cols-[1fr_220px]">
                            <div>
                                <label for="comment-content" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Commentaire</label>
                                <textarea id="comment-content" name="content" rows="4" class="w-full px-4 py-3"
                                    placeholder="Rédigez une mise à jour utile pour l’équipe...">{{ old('content') }}</textarea>
                                @error('content')
                                    <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="comment-author" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Auteur</label>
                                <input id="comment-author" type="text" name="author" class="w-full px-4 py-3"
                                    value="{{ old('author') }}" placeholder="Commentaire author">
                                @error('author')
                                    <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button type="submit" class="btn-primary">Ajouter le commentaire</button>
                            <a href="{{ route('projects.show', $task->project) }}" class="btn-secondary">Voir le projet</a>
                        </div>
                    </form>

                    <div class="mt-8 space-y-4">
                        @forelse ($task->comments as $comment)
                            <article class="rounded-2xl border border-[var(--line)] bg-[var(--card-soft)] p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-[var(--text-strong)]">{{ $comment->author ?: 'Anonyme' }}</span>
                                            <span class="text-xs text-[var(--muted)]">{{ $comment->created_at?->format('d M Y · H:i') }}</span>
                                        </div>
                                        <p class="mt-3 text-sm leading-6 text-[var(--text)]">{{ $comment->content }}</p>
                                    </div>

                                    <form action="{{ route('comments.destroy', $comment) }}" method="POST"
                                        onsubmit="return confirm('Supprimer ce commentaire ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-rose-300 hover:text-rose-200">Supprimer</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="empty-column-card min-h-32">
                                <p>Aucun commentaire. Ajoutez la première mise à jour de cette tâche.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="space-y-4">
                <div class="panel-dark p-6">
                    <h2 class="text-lg font-semibold">Informations sur la tâche</h2>
                    <dl class="mt-4 space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--muted)]">Projet</dt>
                            <dd class="text-right text-[var(--text-strong)]">{{ $task->project?->name ?? 'Projet inconnu' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--muted)]">Date d’échéance</dt>
                            <dd class="text-[var(--text-strong)]">{{ $task->due_date ? \Illuminate\Support\Carbon::parse($task->due_date)->format('d M Y') : 'Non définie' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--muted)]">Responsable</dt>
                            <dd class="text-[var(--text-strong)]">{{ $assigneeName ?: 'Non assigné' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--muted)]">Créé le</dt>
                            <dd class="text-[var(--text-strong)]">{{ $task->created_at?->format('d M Y') }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="panel-dark p-6">
                    <h2 class="text-lg font-semibold">Actions rapides</h2>
                    @if ($canManageTask)
                        <div class="mt-4 flex flex-col gap-3">
                            <a href="{{ route('tasks.edit', $task) }}" class="btn-primary">Mettre à jour la tâche</a>
                            <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                                onsubmit="return confirm('Supprimer cette tâche ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-secondary w-full">Supprimer la tâche</button>
                            </form>
                        </div>
                    @else
                        <p class="mt-4 text-sm leading-6 text-[var(--muted)]">Seul le responsable du projet peut modifier ou supprimer cette tâche.</p>
                    @endif
                </div>
            </aside>
        </div>
    </section>
@endsection
