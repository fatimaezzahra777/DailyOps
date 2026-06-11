@extends('layouts.app')

@section('content')
    @php
        $projectCollection = $allFilteredProjects;
        $queryWithoutStatus = request()->except(['status', 'page']);
        $openModal = session('open_modal');
        $visibleColumnIds = $projectColumns->pluck('id');

        $stats = [
            [
                'label' => 'Total projects',
                'value' => $projectCollection->count(),
                'meta' => $projectCollection->where('created_at', '>=', now()->startOfWeek())->count() . ' added this week',
                'tone' => 'positive',
            ],
            [
                'label' => 'In progress',
                'value' => $projectCollection->where('status', 'in_progress')->count(),
                'meta' => 'Active',
                'tone' => 'neutral',
            ],
            [
                'label' => 'Completed',
                'value' => $projectCollection->where('status', 'completed')->count(),
                'meta' => $projectCollection->count() > 0 ? round(($projectCollection->where('status', 'completed')->count() / max($projectCollection->count(), 1)) * 100) . '% of total' : 'No data yet',
                'tone' => 'neutral',
            ],
            [
                'label' => 'Overdue',
                'value' => $projectCollection
                    ->filter(fn ($project) => $project->end_date && $project->end_date->isPast() && $project->status !== 'completed')
                    ->count(),
                'meta' => 'Needs attention',
                'tone' => 'danger',
            ],
        ];

        $columns = collect([
            [
                'title' => 'Pending projects',
                'status' => 'pending',
                'column_id' => null,
                'dot' => 'bg-[#e8007d]',
                'empty' => 'No pending projects',
                'description' => 'Ideas to validate and brief before production.',
                'laneClass' => 'kanban-lane-pending',
                'badgeClass' => 'kanban-count-pending',
                'cardAccent' => 'project-card-accent-pending',
            ],
            [
                'title' => 'In progress projects',
                'status' => 'in_progress',
                'column_id' => null,
                'dot' => 'bg-[#f59e0b]',
                'empty' => 'No active projects',
                'description' => 'Current execution with active contributors.',
                'laneClass' => 'kanban-lane-progress',
                'badgeClass' => 'kanban-count-progress',
                'cardAccent' => 'project-card-accent-progress',
            ],
            [
                'title' => 'Completed projects',
                'status' => 'completed',
                'column_id' => null,
                'dot' => 'bg-[#00a86b]',
                'empty' => 'No completed projects',
                'description' => 'Delivered work and archived outcomes.',
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
                'empty' => 'No projects in this column',
                'description' => 'Custom workflow column.',
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

    <section class="space-y-6">
        <div class="kanban-overview">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-2xl">
                    <p class="kanban-eyebrow">Kanban board</p>
                    <h1 class="kanban-title">Projects flow</h1>
                    <p class="kanban-subtitle">Visualise the entire project pipeline from idea to delivery, with the platform palette and clearer lane ownership.</p>
                </div>

                <div class="kanban-legend">
                    <span class="kanban-legend-item"><span class="kanban-legend-dot bg-[#e8007d]"></span> Pending</span>
                    <span class="kanban-legend-item"><span class="kanban-legend-dot bg-[#f59e0b]"></span> In progress</span>
                    <span class="kanban-legend-item"><span class="kanban-legend-dot bg-[#00a86b]"></span> Completed</span>
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
                    class="filter-pill {{ request('status') ? '' : 'filter-pill-active' }}">All projects</a>
                <a href="{{ route('projects.index', array_merge($queryWithoutStatus, ['status' => 'pending'])) }}"
                    class="filter-pill {{ request('status') === 'pending' ? 'filter-pill-active' : '' }}">Pending</a>
                <a href="{{ route('projects.index', array_merge($queryWithoutStatus, ['status' => 'in_progress'])) }}"
                    class="filter-pill {{ request('status') === 'in_progress' ? 'filter-pill-active' : '' }}">In progress</a>
                <a href="{{ route('projects.index', array_merge($queryWithoutStatus, ['status' => 'completed'])) }}"
                    class="filter-pill {{ request('status') === 'completed' ? 'filter-pill-active' : '' }}">Completed</a>
            </div>

            <span class="kanban-toolbar-note">{{ $projectCollection->count() }} visible projects</span>
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
                        <button type="button" class="icon-button h-7 w-7 p-0" aria-label="Add project"
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

                                $deadlineLabel = 'No deadline';
                                $deadlineClass = 'project-deadline-neutral';

                                if ($project->end_date) {
                                    if ($project->status !== 'completed' && $project->end_date->isPast()) {
                                        $deadlineLabel = 'Overdue';
                                        $deadlineClass = 'project-deadline-danger';
                                    } elseif ($project->status !== 'completed' && $project->end_date->isToday()) {
                                        $deadlineLabel = 'Due today';
                                        $deadlineClass = 'project-deadline-warning';
                                    } elseif ($project->status !== 'completed' && $project->end_date->diffInDays(now()) <= 3) {
                                        $deadlineLabel = 'Due soon';
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
                                    <a href="{{ route('projects.show', $project) }}" class="task-title text-left hover:text-[#e8007d]">
                                        {{ $project->name }}
                                    </a>
                                    @if ($canManageCard)
                                        <button type="button" class="icon-button h-8 w-8 p-0" aria-label="Edit project" title="Edit project"
                                            data-modal-open="edit-project-modal-{{ $project->id }}">
                                            <span class="material-symbols-rounded text-[18px]">edit</span>
                                        </button>
                                    @endif
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span class="{{ $tagPalette[$project->status] ?? 'tag-chip' }}">
                                        {{ str($project->status)->replace('_', ' ')->title() }}
                                    </span>
                                    <span class="tag-chip">#{{ str_pad((string) $project->id, 2, '0', STR_PAD_LEFT) }}</span>
                                </div>

                                @if ($project->description)
                                    <p class="task-description">{{ \Illuminate\Support\Str::limit($project->description, 96) }}</p>
                                @endif

                                <div class="project-meta-grid">
                                    <div class="project-meta-item">
                                        <span class="project-meta-label">Start</span>
                                        <span class="project-meta-value">{{ $project->start_date ? $project->start_date->format('d M') : 'Not set' }}</span>
                                    </div>
                                    <div class="project-meta-item">
                                        <span class="project-meta-label">Deadline</span>
                                        <span class="project-meta-value {{ $deadlineClass }}">{{ $deadlineLabel }}</span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="mb-2 flex items-center justify-between text-[11px] text-[var(--muted)]">
                                        <span>Progress</span>
                                        <span>{{ $progress }}%</span>
                                    </div>
                                    <div class="progress-track">
                                        <div class="progress-bar" style="width: {{ $progress }}%;"></div>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex items-center gap-3 text-[11px] text-[var(--muted)]">
                                        <span>{{ $project->description ? 'Brief ready' : 'Brief missing' }}</span>
                                        <span>{{ $project->created_at?->diffForHumans() }}</span>
                                    </div>

                                    <span class="text-[11px] text-[var(--muted)]">{{ $project->tasks_count ?? 0 }} tasks</span>
                                </div>

                                <div class="mt-4 flex items-center justify-between gap-3 border-t border-[var(--line)] pt-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('projects.show', $project) }}" class="icon-button h-8 w-8 p-0"
                                            aria-label="View project" title="View project">
                                            <span class="material-symbols-rounded text-[18px]">visibility</span>
                                        </a>
                                        @if ($project->companyLogo())
                                            <span class="project-company-circle project-company-circle-small"
                                                title="{{ $project->companyLabel() }}">
                                                <img src="{{ asset($project->companyLogo()) }}" alt="{{ $project->companyLabel() }}">
                                            </span>
                                        @endif
                                    </div>

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
                            + Add project
                        </button>
                    </div>
                </section>
            @endforeach

                <section class="board-column kanban-lane kanban-lane-empty">
                    <div class="kanban-lane-head">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-zinc-400"></span>
                            <div class="min-w-0">
                                <h2 class="board-column-title">New column</h2>
                                <p class="kanban-lane-description">Extend your workflow with a custom lane.</p>
                            </div>
                        </div>
                        <button type="button" class="icon-button h-7 w-7 p-0" aria-label="Add column"
                            data-modal-open="create-column-modal">
                            <span class="text-sm leading-none">+</span>
                        </button>
                    </div>

                    <button type="button" class="empty-column-card w-full"
                        data-modal-open="create-column-modal">
                        <p>Click to add a new board column</p>
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
                    <p class="modal-eyebrow">Create project</p>
                    <h2 class="modal-title">New project</h2>
                    <p class="modal-subtitle">Create a project without leaving the board.</p>
                </div>
                <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
            </div>

            <form action="{{ route('projects.store') }}" method="POST" class="space-y-5" autocomplete="off" spellcheck="false">
                @csrf
                <input type="text" tabindex="-1" autocomplete="username" class="hidden" aria-hidden="true">
                <input type="password" tabindex="-1" autocomplete="new-password" class="hidden" aria-hidden="true">

                @if ($errors->getBag('createProject')->any())
                    <div class="rounded-md border border-red-600/20 bg-red-600/10 p-4 text-sm text-red-600">
                        <p class="font-medium text-red-700">Please fix the following errors:</p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($errors->getBag('createProject')->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="create-project-name" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Name</label>
                        <input id="create-project-name" name="create_name" type="text" class="w-full px-4 py-3"
                            value="{{ $openModal === 'create-project-modal' ? old('create_name') : '' }}"
                            data-field-default="" autocomplete="new-password" required>
                    </div>

                    <div class="md:col-span-2">
                        <label for="create-project-description" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Description</label>
                        <textarea id="create-project-description" name="create_description" rows="4" class="w-full px-4 py-3"
                            data-field-default="" autocomplete="off">{{ $openModal === 'create-project-modal' ? old('create_description') : '' }}</textarea>
                    </div>

                    @include('projects.partials.company-selector', [
                        'companyFieldName' => 'create_company',
                        'companyPrefix' => 'create-project',
                        'selectedCompany' => $openModal === 'create-project-modal' ? old('create_company') : null,
                    ])

                    <div>
                        <label for="create-project-status" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Status</label>
                        <select id="create-project-status" name="create_status" class="w-full px-4 py-3"
                            data-field-default="pending" autocomplete="off">
                            @foreach (['pending' => 'Pending', 'in_progress' => 'In progress', 'completed' => 'Completed'] as $value => $label)
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
                        <label for="create-project-start-date" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Start date</label>
                        <input id="create-project-start-date" name="create_start_date" type="date" class="w-full px-4 py-3"
                            value="{{ $openModal === 'create-project-modal' ? old('create_start_date') : '' }}"
                            data-field-default="" autocomplete="off">
                    </div>

                    <div>
                        <label for="create-project-end-date" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">End date</label>
                        <input id="create-project-end-date" name="create_end_date" type="date" class="w-full px-4 py-3"
                            value="{{ $openModal === 'create-project-modal' ? old('create_end_date') : '' }}"
                            data-field-default="" autocomplete="off">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-primary">Save project</button>
                    <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
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
                    <p class="modal-eyebrow">Board column</p>
                    <h2 class="modal-title">New column</h2>
                    <p class="modal-subtitle">Add a custom lane to your project workflow.</p>
                </div>
                <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
            </div>

            <form action="{{ route('projects.columns.store') }}" method="POST" class="space-y-5">
                @csrf

                @if ($errors->getBag('createColumn')->any())
                    <div class="rounded-md border border-red-600/20 bg-red-600/10 p-4 text-sm text-red-600">
                        {{ $errors->getBag('createColumn')->first() }}
                    </div>
                @endif

                <div>
                    <label for="create-column-name" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Name</label>
                    <input id="create-column-name" name="name" type="text" class="w-full px-4 py-3"
                        value="{{ $openModal === 'create-column-modal' ? old('name') : '' }}"
                        data-field-default="" required>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-primary">Add column</button>
                    <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
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
                        <p class="modal-eyebrow">Project details</p>
                        <h2 class="modal-title">{{ $project->name }}</h2>
                        <p class="modal-subtitle">Quick overview, dates and actions in one place.</p>
                    </div>
                    <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
                </div>

                <div class="grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
                    <article class="panel-dark p-5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="{{ $tagPalette[$project->status] ?? 'tag-chip' }}">
                                {{ str($project->status)->replace('_', ' ')->title() }}
                            </span>
                            @if ($project->companyLogo())
                                <span class="inline-flex h-10 w-24 items-center justify-center rounded-md border border-[var(--line)] bg-white px-2">
                                    <img src="{{ asset($project->companyLogo()) }}" alt="{{ $project->companyLabel() }}"
                                        class="max-h-7 max-w-full object-contain">
                                </span>
                            @endif
                        </div>

                        <p class="mt-4 text-sm leading-7 text-[var(--text)]">
                            {{ $project->description ?: 'No description has been added for this project yet.' }}
                        </p>
                    </article>

                    <aside class="space-y-4">
                        <div class="panel-dark p-5">
                            <h3 class="text-sm font-semibold text-[var(--text-strong)]">Timeline</h3>
                            <dl class="mt-4 space-y-3 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-[var(--muted)]">Start date</dt>
                                    <dd class="text-[var(--text-strong)]">{{ $project->start_date?->format('d M Y') ?? 'Not set' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-[var(--muted)]">End date</dt>
                                    <dd class="text-[var(--text-strong)]">{{ $project->end_date?->format('d M Y') ?? 'Not set' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-[var(--muted)]">Created at</dt>
                                    <dd class="text-[var(--text-strong)]">{{ $project->created_at?->format('d M Y') ?? 'Unknown' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="modal-actions">
                            <a href="{{ route('projects.show', $project) }}" class="icon-button h-10 w-10 p-0"
                                aria-label="View project" title="View project">
                                <span class="material-symbols-rounded text-[20px]">visibility</span>
                            </a>
                            <button type="button" class="icon-button h-10 w-10 p-0" data-modal-switch="{{ $editModalId }}"
                                aria-label="Edit project" title="Edit project">
                                <span class="material-symbols-rounded text-[20px]">edit</span>
                            </button>
                            <button type="button" class="icon-button h-10 w-10 p-0" data-modal-switch="{{ $deleteModalId }}"
                                aria-label="Delete project" title="Delete project">
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
                        <p class="modal-eyebrow">Update project</p>
                        <h2 class="modal-title">{{ $project->name }}</h2>
                        <p class="modal-subtitle">Edit the project directly from the board.</p>
                    </div>
                    <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
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
                        <button type="submit" class="btn-primary">Update project</button>
                        <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
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
                        <p class="modal-eyebrow text-red-600">Delete project</p>
                        <h2 class="modal-title">{{ $project->name }}</h2>
                        <p class="modal-subtitle">This action cannot be undone.</p>
                    </div>
                    <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
                </div>

                <div class="rounded-md border border-red-600/15 bg-red-600/10 p-4 text-sm text-[var(--text)]">
                    You are about to delete this project and remove it from the board.
                </div>

                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="modal-actions">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-primary">Delete project</button>
                    <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                </form>
            </div>
        </div>
    @endforeach
@endsection
