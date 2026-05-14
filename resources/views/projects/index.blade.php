@extends('layouts.app')

@section('content')
    @php
        $projectCollection = $allFilteredProjects;
        $queryWithoutStatus = request()->except(['status', 'page']);
        $openModal = session('open_modal');

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

        $columns = [
            ['title' => 'Pending projects', 'status' => 'pending', 'dot' => 'bg-violet-500', 'empty' => 'No pending projects'],
            ['title' => 'In progress projects', 'status' => 'in_progress', 'dot' => 'bg-emerald-500', 'empty' => 'No active projects'],
            ['title' => 'Completed projects', 'status' => 'completed', 'dot' => 'bg-rose-500', 'empty' => 'No completed projects'],
            ['title' => 'New column', 'status' => null, 'dot' => 'bg-zinc-500', 'empty' => 'Create a project to fill this column'],
        ];

        $tagPalette = [
            'pending' => 'tag-chip tag-chip-violet',
            'in_progress' => 'tag-chip tag-chip-amber',
            'completed' => 'tag-chip tag-chip-emerald',
        ];
    @endphp

    <section class="space-y-6">
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

        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
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

            <div class="avatar-stack self-end">
                @foreach (['A', 'B', 'C', '+'] as $avatar)
                    <span class="avatar-dot">{{ $avatar }}</span>
                @endforeach
            </div>
        </div>

        <div class="board-grid custom-scroll overflow-x-auto pb-4">
            @foreach ($columns as $column)
                @php
                    $items = $column['status']
                        ? $projectCollection->where('status', $column['status'])->values()
                        : collect();
                @endphp
                <section class="board-column">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full {{ $column['dot'] }}"></span>
                            <h2 class="board-column-title">{{ $column['title'] }}</h2>
                            <span class="board-column-count">{{ $items->count() }}</span>
                        </div>
                        <button type="button" class="icon-button h-7 w-7 p-0" aria-label="Add project"
                            data-modal-open="create-project-modal">
                            <span class="text-sm leading-none">+</span>
                        </button>
                    </div>

                    <div class="space-y-3">
                        @forelse ($items as $project)
                            @php
                                $progress = match ($project->status) {
                                    'completed' => 100,
                                    'in_progress' => 68,
                                    default => 28,
                                };
                            @endphp
                            <article class="task-card">
                                <div class="flex items-start justify-between gap-3">
                                    <button type="button" class="task-title text-left hover:text-violet-300"
                                        data-modal-open="project-details-modal-{{ $project->id }}">
                                        {{ $project->name }}
                                    </button>
                                    <button type="button" class="task-menu" aria-label="Edit project"
                                        data-modal-open="edit-project-modal-{{ $project->id }}">
                                        Edit
                                    </button>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span class="{{ $tagPalette[$project->status] ?? 'tag-chip' }}">
                                        {{ str($project->status)->replace('_', ' ')->title() }}
                                    </span>
                                    @if ($project->assigned_to)
                                        <span class="tag-chip">{{ $project->assigned_to }}</span>
                                    @endif
                                </div>

                                @if ($project->description)
                                    <p class="task-description">{{ \Illuminate\Support\Str::limit($project->description, 88) }}</p>
                                @endif

                                <div class="mt-4">
                                    <div class="mb-2 flex items-center justify-between text-[11px] text-[var(--muted)]">
                                        <span>{{ $project->end_date ? $project->end_date->format('d M') : 'No deadline' }}</span>
                                        <span>{{ $progress }}%</span>
                                    </div>
                                    <div class="progress-track">
                                        <div class="progress-bar" style="width: {{ $progress }}%;"></div>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex items-center gap-3 text-[11px] text-[var(--muted)]">
                                        <span>{{ $project->start_date ? $project->start_date->format('d M') : 'No start' }}</span>
                                        <span>{{ $project->description ? '1 note' : '0 notes' }}</span>
                                    </div>

                                    <div class="flex -space-x-2">
                                        <span class="mini-avatar">A</span>
                                        <span class="mini-avatar mini-avatar-secondary">B</span>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between gap-3 border-t border-[var(--line)] pt-4">
                                    <button type="button" class="text-xs font-medium text-[var(--muted)] hover:text-[var(--text-strong)]"
                                        data-modal-open="project-details-modal-{{ $project->id }}">
                                        View details
                                    </button>

                                    <button type="button" class="text-xs font-medium text-rose-300 hover:text-rose-200"
                                        data-modal-open="delete-project-modal-{{ $project->id }}">
                                        Delete
                                    </button>
                                </div>
                            </article>
                        @empty
                            <div class="empty-column-card">
                                <p>{{ $column['empty'] }}</p>
                            </div>
                        @endforelse

                        <button type="button" class="board-add-card inline-flex items-center justify-center"
                            data-modal-open="create-project-modal">
                            + Add project
                        </button>
                    </div>
                </section>
            @endforeach
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
                    <div class="rounded-2xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-200">
                        <p class="font-medium text-rose-100">Please fix the following errors:</p>
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

                    <div>
                        <label for="create-project-assigned-to" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Assigned to</label>
                        <input id="create-project-assigned-to" name="create_assigned_to" type="text" class="w-full px-4 py-3"
                            value="{{ $openModal === 'create-project-modal' ? old('create_assigned_to') : '' }}"
                            data-field-default="" autocomplete="new-password">
                    </div>

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
                            @if ($project->assigned_to)
                                <span class="tag-chip">{{ $project->assigned_to }}</span>
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

                        <div class="modal-actions modal-actions-stacked">
                            <button type="button" class="btn-primary" data-modal-switch="{{ $editModalId }}">Edit project</button>
                            <button type="button" class="btn-secondary" data-modal-switch="{{ $deleteModalId }}">Delete project</button>
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
                        <p class="modal-eyebrow text-rose-300">Delete project</p>
                        <h2 class="modal-title">{{ $project->name }}</h2>
                        <p class="modal-subtitle">This action cannot be undone.</p>
                    </div>
                    <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
                </div>

                <div class="rounded-2xl border border-rose-500/15 bg-rose-500/10 p-4 text-sm text-[var(--text)]">
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
