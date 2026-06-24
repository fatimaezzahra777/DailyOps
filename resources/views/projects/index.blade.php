@extends('layouts.app')

@section('content')
    @php
        $projectCollection = $allFilteredProjects;
        $queryWithoutStatus = request()->except(['status', 'page']);
        $openModal = session('open_modal');
        $visibleColumnIds = $projectColumns->pluck('id');

        $stats = [
            [
                'label' => 'Total des projets',
                'value' => $projectCollection->count(),
                'meta' => $projectCollection->where('created_at', '>=', now()->startOfWeek())->count() . ' ajoutés cette semaine',
                'tone' => 'positive',
            ],
            [
                'label' => 'En cours',
                'value' => $projectCollection->where('status', 'in_progress')->count(),
                'meta' => 'Actifs',
                'tone' => 'neutral',
            ],
            [
                'label' => 'Terminé',
                'value' => $projectCollection->where('status', 'completed')->count(),
                'meta' => $projectCollection->count() > 0 ? round(($projectCollection->where('status', 'completed')->count() / max($projectCollection->count(), 1)) * 100) . ' % du total' : 'Aucune donnée',
                'tone' => 'neutral',
            ],
            [
                'label' => 'En retard',
                'value' => $projectCollection
                    ->filter(fn ($project) => $project->end_date && $project->end_date->isPast() && $project->status !== 'completed')
                    ->count(),
                'meta' => 'À surveiller',
                'tone' => 'danger',
            ],
        ];

        $columns = collect([
            [
                'title' => 'Projets en attente',
                'status' => 'pending',
                'column_id' => null,
                'dot' => 'bg-[#c50064]',
                'empty' => 'Aucun projet en attente',
                'description' => 'Idées à valider et à préparer avant la production.',
                'laneClass' => 'kanban-lane-pending',
                'badgeClass' => 'kanban-count-pending',
                'cardAccent' => 'project-card-accent-pending',
            ],
            [
                'title' => 'Projets en cours',
                'status' => 'in_progress',
                'column_id' => null,
                'dot' => 'bg-[#f59e0b]',
                'empty' => 'Aucun projet en cours',
                'description' => 'Projets en cours de réalisation.',
                'laneClass' => 'kanban-lane-progress',
                'badgeClass' => 'kanban-count-progress',
                'cardAccent' => 'project-card-accent-progress',
            ],
            [
                'title' => 'Projets terminés',
                'status' => 'completed',
                'column_id' => null,
                'dot' => 'bg-[#00a86b]',
                'empty' => 'Aucun projet terminé',
                'description' => 'Travaux livrés et résultats archivés.',
                'laneClass' => 'kanban-lane-completed',
                'badgeClass' => 'kanban-count-completed',
                'cardAccent' => 'project-card-accent-completed',
            ],
        ])->merge(
            $projectColumns->map(fn ($column) => [
                'title' => $column->name,
                'status' => null,
                'column_id' => $column->id,
                'dot' => 'bg-sky-500',
                'empty' => 'Aucun projet dans cette colonne',
                'description' => 'Colonne personnalisée.',
                'laneClass' => 'kanban-lane-empty',
                'badgeClass' => 'kanban-count-empty',
                'cardAccent' => 'project-card-accent-empty',
            ])
        );

        $tagPalette = [
            'pending' => 'tag-chip tag-chip-violet',
            'in_progress' => 'tag-chip tag-chip-amber',
            'completed' => 'tag-chip tag-chip-emerald',
        ];
    @endphp

    <section class="space-y-6" data-kanban-animate>
        <div class="kanban-overview">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-2xl">
                    <p class="kanban-eyebrow">Tableau Kanban</p>
                    <h1 class="kanban-title">Suivi des projets</h1>
                    <p class="kanban-subtitle">Visualisez l’avancement de tous les projets, de la préparation à la livraison.</p>
                </div>

                <div class="kanban-legend">
                    <span class="kanban-legend-item"><span class="kanban-legend-dot bg-[#c50064]"></span> En attente</span>
                    <span class="kanban-legend-item"><span class="kanban-legend-dot bg-[#f59e0b]"></span> En cours</span>
                    <span class="kanban-legend-item"><span class="kanban-legend-dot bg-[#00a86b]"></span> Terminé</span>
                </div>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-4">
            @foreach ($stats as $index => $stat)
                <article class="metric-card {{ $index === 0 ? 'metric-card-featured' : '' }}">
                    <p class="metric-label">{{ $stat['label'] }}</p>
                    <div class="mt-4 flex items-end justify-between gap-3">
                        <p class="metric-value">{{ $stat['value'] }}</p>
                    </div>
                    <p class="mt-2 text-xs {{ $stat['tone'] === 'danger' ? 'text-rose-400' : 'text-[var(--muted)]' }}">
                        {{ $stat['meta'] }}
                    </p>
                </article>
            @endforeach
        </div>

        <div class="kanban-toolbar">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('projects.index', $queryWithoutStatus) }}"
                    class="filter-pill {{ request('status') ? '' : 'filter-pill-active' }}">Tous les projets</a>
                <a href="{{ route('projects.index', array_merge($queryWithoutStatus, ['status' => 'pending'])) }}"
                    class="filter-pill {{ request('status') === 'pending' ? 'filter-pill-active' : '' }}">En attente</a>
                <a href="{{ route('projects.index', array_merge($queryWithoutStatus, ['status' => 'in_progress'])) }}"
                    class="filter-pill {{ request('status') === 'in_progress' ? 'filter-pill-active' : '' }}">En cours</a>
                <a href="{{ route('projects.index', array_merge($queryWithoutStatus, ['status' => 'completed'])) }}"
                    class="filter-pill {{ request('status') === 'completed' ? 'filter-pill-active' : '' }}">Terminé</a>
            </div>

            <span class="kanban-toolbar-note">{{ $projectCollection->count() }} projets visibles</span>
        </div>

        <div class="kanban-shell custom-scroll overflow-x-auto pb-4" data-board data-projects-base-url="{{ url('/projects') }}">
            <div class="board-grid">
            @foreach ($columns as $column)
                @php
                    $items = $column['column_id']
                        ? $projectCollection->where('column_id', $column['column_id'])->values()
                        : $projectCollection
                            ->where('status', $column['status'])
                            ->filter(fn ($project) => blank($project->column_id) || ! $visibleColumnIds->contains($project->column_id))
                            ->values();
                @endphp
                <section class="board-column kanban-lane {{ $column['laneClass'] }}">
                    <div class="kanban-lane-head">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full {{ $column['dot'] }}"></span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="board-column-title">{{ $column['title'] }}</h2>
                                    <span class="board-column-count {{ $column['badgeClass'] }}">{{ $items->count() }}</span>
                                </div>
                                <p class="kanban-lane-description">{{ $column['description'] }}</p>
                            </div>
                        </div>
                        <button type="button" class="icon-button h-7 w-7 p-0" aria-label="Ajouter un projet"
                            data-modal-open="create-project-modal"
                            data-create-status="{{ $column['status'] ?? 'pending' }}"
                            data-create-column-id="{{ $column['column_id'] }}">
                            <span class="text-sm leading-none">+</span>
                        </button>
                    </div>

                    <div class="board-drop-zone space-y-3"
                        data-drop-zone
                        data-drop-status="{{ $column['status'] ?? '' }}"
                        data-drop-column-id="{{ $column['column_id'] ?? '' }}">
                        @forelse ($items as $project)
                            @php
                                $progress = match ($project->status) {
                                    'completed' => 100,
                                    'in_progress' => 68,
                                    default => 28,
                                };

                                $deadlineLabel = 'Aucune échéance';
                                $deadlineClass = 'project-deadline-neutral';

                                if ($project->end_date) {
                                    if ($project->status !== 'completed' && $project->end_date->isPast()) {
                                        $deadlineLabel = 'En retard';
                                        $deadlineClass = 'project-deadline-danger';
                                    } elseif ($project->status !== 'completed' && $project->end_date->isToday()) {
                                        $deadlineLabel = 'Échéance aujourd’hui';
                                        $deadlineClass = 'project-deadline-warning';
                                    } elseif ($project->status !== 'completed' && $project->end_date->diffInDays(now()) <= 3) {
                                        $deadlineLabel = 'Échéance proche';
                                        $deadlineClass = 'project-deadline-warning';
                                    } else {
                                        $deadlineLabel = $project->end_date->format('d M');
                                    }
                                }
                                $canManageCard = $project->isManagedBy(auth()->user());
                            @endphp
                            <article class="task-card project-card {{ $column['cardAccent'] }}" draggable="true"
                                data-draggable-project data-project-id="{{ $project->id }}">
                                <div class="flex items-start justify-between gap-3">
                                    <a href="{{ route('projects.show', $project) }}" class="task-title text-left hover:text-[#c50064]">
                                        {{ $project->name }}
                                    </a>
                                    @if ($canManageCard)
                                        <button type="button" class="icon-button h-8 w-8 p-0" aria-label="Modifier le projet" title="Modifier le projet"
                                            data-modal-open="edit-project-modal-{{ $project->id }}">
                                            <span class="material-symbols-rounded text-[18px]">edit</span>
                                        </button>
                                    @endif
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span class="{{ $tagPalette[$project->status] ?? 'tag-chip' }}">
                                        {{ ['pending' => 'En attente', 'in_progress' => 'En cours', 'completed' => 'Terminé'][$project->status] ?? $project->status }}
                                    </span>
                                    <span class="tag-chip">#{{ str_pad((string) $project->id, 2, '0', STR_PAD_LEFT) }}</span>
                                </div>

                                @if ($project->description)
                                    <p class="task-description">{{ \Illuminate\Support\Str::limit($project->description, 96) }}</p>
                                @endif

                                <div class="project-meta-grid">
                                    <div class="project-meta-item">
                                        <span class="project-meta-label">Début</span>
                                        <span class="project-meta-value">{{ $project->start_date ? $project->start_date->format('d M') : 'Non définie' }}</span>
                                    </div>
                                    <div class="project-meta-item">
                                        <span class="project-meta-label">Échéance</span>
                                        <span class="project-meta-value {{ $deadlineClass }}">{{ $deadlineLabel }}</span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="mb-2 flex items-center justify-between text-[11px] text-[var(--muted)]">
                                        <span>Progression</span>
                                        <span>{{ $progress }}%</span>
                                    </div>
                                    <div class="progress-track">
                                        <div class="progress-bar" style="width: {{ $progress }}%;"></div>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex items-center gap-3 text-[11px] text-[var(--muted)]">
                                        <span>{{ $project->description ? 'Description renseignée' : 'Description manquante' }}</span>
                                        <span>{{ $project->created_at?->diffForHumans() }}</span>
                                    </div>

                                    <span class="text-[11px] text-[var(--muted)]">{{ $project->tasks_count ?? 0 }} tâches</span>
                                </div>

                                <div class="mt-4 flex items-center justify-between gap-3 border-t border-[var(--line)] pt-4">
                                    <a href="{{ route('projects.show', $project) }}" class="icon-button h-8 w-8 p-0"
                                        aria-label="Voir le projet" title="Voir le projet">
                                        <span class="material-symbols-rounded text-[18px]">visibility</span>
                                    </a>

                                    @if ($canManageCard)
                                        <button type="button" class="icon-button h-8 w-8 p-0"
                                            data-modal-open="delete-project-modal-{{ $project->id }}">
                                            <span class="material-symbols-rounded text-[18px]">delete</span>
                                        </button>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="empty-column-card">
                                <p>{{ $column['empty'] }}</p>
                            </div>
                        @endforelse

                        <button type="button" class="board-add-card inline-flex items-center justify-center"
                            data-modal-open="create-project-modal"
                            data-create-status="{{ $column['status'] ?? 'pending' }}"
                            data-create-column-id="{{ $column['column_id'] }}">
                            + Ajouter un projet
                        </button>
                    </div>
                </section>
            @endforeach

                <section class="board-column kanban-lane kanban-lane-empty">
                    <div class="kanban-lane-head">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-zinc-400"></span>
                            <div class="min-w-0">
                                <h2 class="board-column-title">Nouvelle colonne</h2>
                                <p class="kanban-lane-description">Ajoutez une colonne personnalisée.</p>
                            </div>
                        </div>
                        <button type="button" class="icon-button h-7 w-7 p-0" aria-label="Ajouter une colonne"
                            data-modal-open="create-column-modal">
                            <span class="text-sm leading-none">+</span>
                        </button>
                    </div>

                    <button type="button" class="empty-column-card w-full"
                        data-modal-open="create-column-modal">
                        <p>Cliquez pour ajouter une colonne</p>
                    </button>
                </section>
            </div>
        </div>

        @if ($projects->hasPages())
            <div class="pt-2">
                {{ $projects->links() }}
            </div>
        @endif
    </section>

    <div class="modal-shell {{ $openModal === 'create-project-modal' ? '' : 'hidden' }}" id="create-project-modal"
        data-reset-on-open="true"
        data-modal tabindex="-1" aria-hidden="{{ $openModal === 'create-project-modal' ? 'false' : 'true' }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-panel modal-panel-form">
            <div class="modal-header">
                <div>
                    <p class="modal-eyebrow">Créer un projet</p>
                    <h2 class="modal-title">Nouveau projet</h2>
                    <p class="modal-subtitle">Créez un projet sans quitter le Kanban.</p>
                </div>
                <button type="button" class="modal-close" data-modal-close aria-label="Fermer la fenêtre">×</button>
            </div>

            <form action="{{ route('projects.store') }}" method="POST" class="space-y-5" autocomplete="off" spellcheck="false">
                @csrf
                <input type="text" tabindex="-1" autocomplete="username" class="hidden" aria-hidden="true">
                <input type="password" tabindex="-1" autocomplete="new-password" class="hidden" aria-hidden="true">

                @if ($errors->getBag('createProject')->any())
                    <div class="rounded-md border border-red-600/20 bg-red-600/10 p-4 text-sm text-red-600">
                        <p class="font-medium text-red-700">Veuillez corriger les erreurs suivantes :</p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($errors->getBag('createProject')->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="create-project-name" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Nom</label>
                        <input id="create-project-name" name="create_name" type="text" class="w-full px-4 py-3"
                            value="{{ $openModal === 'create-project-modal' ? old('create_name') : '' }}"
                            data-field-default="" autocomplete="new-password" required>
                    </div>

                    <div class="md:col-span-2">
                        <label for="create-project-description" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Description</label>
                        <textarea id="create-project-description" name="create_description" rows="4" class="w-full px-4 py-3"
                            data-field-default="" autocomplete="off">{{ $openModal === 'create-project-modal' ? old('create_description') : '' }}</textarea>
                    </div>

                    <div>
                        <label for="create-project-status" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Statut</label>
                        <select id="create-project-status" name="create_status" class="w-full px-4 py-3"
                            data-field-default="pending" autocomplete="off">
                            @foreach (['pending' => 'En attente', 'in_progress' => 'En cours', 'completed' => 'Terminé'] as $value => $label)
                                <option value="{{ $value }}" @selected(($openModal === 'create-project-modal' ? old('create_status', 'pending') : 'pending') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <input id="create-project-column-id" name="create_column_id" type="hidden"
                        value="{{ $openModal === 'create-project-modal' ? old('create_column_id') : '' }}"
                        data-field-default="">

                    <div>
                        <label for="create-project-start-date" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Date de début</label>
                        <input id="create-project-start-date" name="create_start_date" type="date" class="w-full px-4 py-3"
                            value="{{ $openModal === 'create-project-modal' ? old('create_start_date') : '' }}"
                            data-field-default="" autocomplete="off">
                    </div>

                    <div>
                        <label for="create-project-end-date" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Date de fin</label>
                        <input id="create-project-end-date" name="create_end_date" type="date" class="w-full px-4 py-3"
                            value="{{ $openModal === 'create-project-modal' ? old('create_end_date') : '' }}"
                            data-field-default="" autocomplete="off">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-primary">Enregistrer le projet</button>
                    <button type="button" class="btn-secondary" data-modal-close>Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-shell {{ $openModal === 'create-column-modal' ? '' : 'hidden' }}" id="create-column-modal"
        data-reset-on-open="true"
        data-modal tabindex="-1" aria-hidden="{{ $openModal === 'create-column-modal' ? 'false' : 'true' }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-panel modal-panel-compact">
            <div class="modal-header">
                <div>
                    <p class="modal-eyebrow">Kanban column</p>
                    <h2 class="modal-title">Nouvelle colonne</h2>
                    <p class="modal-subtitle">Ajoutez une colonne personnalisée au suivi de vos projets.</p>
                </div>
                <button type="button" class="modal-close" data-modal-close aria-label="Fermer la fenêtre">×</button>
            </div>

            <form action="{{ route('projects.columns.store') }}" method="POST" class="space-y-5">
                @csrf

                @if ($errors->getBag('createColonne')->any())
                    <div class="rounded-md border border-red-600/20 bg-red-600/10 p-4 text-sm text-red-600">
                        {{ $errors->getBag('createColonne')->first() }}
                    </div>
                @endif

                <div>
                    <label for="create-column-name" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Nom</label>
                    <input id="create-column-name" name="name" type="text" class="w-full px-4 py-3"
                        value="{{ $openModal === 'create-column-modal' ? old('name') : '' }}"
                        data-field-default="" required>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-primary">Ajouter une colonne</button>
                    <button type="button" class="btn-secondary" data-modal-close>Annuler</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($projectCollection as $project)
        @php
            $detailModalId = "project-details-modal-{$project->id}";
            $editModalId = "edit-project-modal-{$project->id}";
            $deleteModalId = "delete-project-modal-{$project->id}";
            $editBag = "updateProject.{$project->id}";
        @endphp

        <div class="modal-shell {{ $openModal === $detailModalId ? '' : 'hidden' }}" id="{{ $detailModalId }}" data-modal
            tabindex="-1" aria-hidden="{{ $openModal === $detailModalId ? 'false' : 'true' }}">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-panel">
                <div class="modal-header">
                    <div>
                        <p class="modal-eyebrow">Projet details</p>
                        <h2 class="modal-title">{{ $project->name }}</h2>
                        <p class="modal-subtitle">Consultez les informations, les dates et les actions du projet.</p>
                    </div>
                    <button type="button" class="modal-close" data-modal-close aria-label="Fermer la fenêtre">×</button>
                </div>

                <div class="grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
                    <article class="panel-dark p-5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="{{ $tagPalette[$project->status] ?? 'tag-chip' }}">
                                {{ ['pending' => 'En attente', 'in_progress' => 'En cours', 'completed' => 'Terminé'][$project->status] ?? $project->status }}
                            </span>
                        </div>

                        <p class="mt-4 text-sm leading-7 text-[var(--text)]">
                            {{ $project->description ?: 'Aucune description n’a encore été ajoutée à ce projet.' }}
                        </p>
                    </article>

                    <aside class="space-y-4">
                        <div class="panel-dark p-5">
                            <h3 class="text-sm font-semibold text-[var(--text-strong)]">Timeline</h3>
                            <dl class="mt-4 space-y-3 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-[var(--muted)]">Date de début</dt>
                                    <dd class="text-[var(--text-strong)]">{{ $project->start_date?->format('d M Y') ?? 'Non définie' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-[var(--muted)]">Date de fin</dt>
                                    <dd class="text-[var(--text-strong)]">{{ $project->end_date?->format('d M Y') ?? 'Non définie' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-[var(--muted)]">Créé le</dt>
                                    <dd class="text-[var(--text-strong)]">{{ $project->created_at?->format('d M Y') ?? 'Inconnue' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="modal-actions">
                            <a href="{{ route('projects.show', $project) }}" class="icon-button h-10 w-10 p-0"
                                aria-label="Voir le projet" title="Voir le projet">
                                <span class="material-symbols-rounded text-[20px]">visibility</span>
                            </a>
                            <button type="button" class="icon-button h-10 w-10 p-0" data-modal-switch="{{ $editModalId }}"
                                aria-label="Modifier le projet" title="Modifier le projet">
                                <span class="material-symbols-rounded text-[20px]">edit</span>
                            </button>
                            <button type="button" class="icon-button h-10 w-10 p-0" data-modal-switch="{{ $deleteModalId }}"
                                aria-label="Supprimer le projet" title="Supprimer le projet">
                                <span class="material-symbols-rounded text-[20px]">delete</span>
                            </button>
                        </div>
                    </aside>
                </div>
            </div>
        </div>

        <div class="modal-shell {{ $openModal === $editModalId ? '' : 'hidden' }}" id="{{ $editModalId }}" data-modal
            tabindex="-1" aria-hidden="{{ $openModal === $editModalId ? 'false' : 'true' }}">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-panel modal-panel-form">
                <div class="modal-header">
                    <div>
                        <p class="modal-eyebrow">Mettre à jour le projet</p>
                        <h2 class="modal-title">{{ $project->name }}</h2>
                        <p class="modal-subtitle">Modifiez le projet directement depuis le Kanban.</p>
                    </div>
                    <button type="button" class="modal-close" data-modal-close aria-label="Fermer la fenêtre">×</button>
                </div>

                <form action="{{ route('projects.update', $project) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')
                    @include('projects.partials.form', [
                        'project' => $project,
                        'prefix' => "edit-project-{$project->id}",
                        'errorBag' => $editBag,
                        'useOldValues' => $openModal === $editModalId,
                    ])

                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Mettre à jour le projet</button>
                        <button type="button" class="btn-secondary" data-modal-close>Annuler</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-shell {{ $openModal === $deleteModalId ? '' : 'hidden' }}" id="{{ $deleteModalId }}" data-modal
            tabindex="-1" aria-hidden="{{ $openModal === $deleteModalId ? 'false' : 'true' }}">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-panel modal-panel-compact">
                <div class="modal-header">
                    <div>
                        <p class="modal-eyebrow text-red-600">Supprimer le projet</p>
                        <h2 class="modal-title">{{ $project->name }}</h2>
                        <p class="modal-subtitle">Cette action est définitive.</p>
                    </div>
                    <button type="button" class="modal-close" data-modal-close aria-label="Fermer la fenêtre">×</button>
                </div>

                <div class="rounded-md border border-red-600/15 bg-red-600/10 p-4 text-sm text-[var(--text)]">
                    Vous allez supprimer définitivement ce projet du Kanban.
                </div>

                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="modal-actions">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-primary">Supprimer le projet</button>
                    <button type="button" class="btn-secondary" data-modal-close>Annuler</button>
                </form>
            </div>
        </div>
    @endforeach
@endsection
