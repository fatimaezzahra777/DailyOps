@extends('layouts.app')

@section('content')
    @php
        $projectCollection = collect($projects->items());

        $stats = [
            [
                'label' => 'Total tasks',
                'value' => $projects->total(),
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
                'meta' => $projects->total() > 0 ? round(($projectCollection->where('status', 'completed')->count() / max($projects->total(), 1)) * 100) . '% of total' : 'No data yet',
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
            ['title' => 'Research', 'status' => 'pending', 'dot' => 'bg-violet-500', 'empty' => 'No pending tasks'],
            ['title' => 'Production', 'status' => 'in_progress', 'dot' => 'bg-emerald-500', 'empty' => 'No active tasks'],
            ['title' => 'Review', 'status' => 'completed', 'dot' => 'bg-rose-500', 'empty' => 'No completed tasks'],
            ['title' => 'New column', 'status' => null, 'dot' => 'bg-zinc-500', 'empty' => 'Drop future tasks here'],
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
                <a href="{{ route('projects.index') }}"
                    class="filter-pill {{ request('status') ? '' : 'filter-pill-active' }}">All tasks</a>
                <a href="{{ route('projects.index', ['status' => 'pending']) }}"
                    class="filter-pill {{ request('status') === 'pending' ? 'filter-pill-active' : '' }}">Pending</a>
                <a href="{{ route('projects.index', ['status' => 'in_progress']) }}"
                    class="filter-pill {{ request('status') === 'in_progress' ? 'filter-pill-active' : '' }}">In progress</a>
                <a href="{{ route('projects.index', ['status' => 'completed']) }}"
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
                        <button class="icon-button h-7 w-7 p-0" type="button" aria-label="Add card">
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
                                    <h3 class="task-title">{{ $project->name }}</h3>
                                    <button class="task-menu" type="button" aria-label="Open card options">•••</button>
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
                            </article>
                        @empty
                            <div class="empty-column-card">
                                <p>{{ $column['empty'] }}</p>
                            </div>
                        @endforelse

                        <button class="board-add-card" type="button">+ Add task</button>
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
@endsection
