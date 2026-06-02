@extends('layouts.app')

@section('content')
    @php
        $month = $month->copy()->startOfMonth();
        $calendarStart = $month->copy()->startOfWeek();
        $days = collect(range(0, 41))->map(fn ($day) => $calendarStart->copy()->addDays($day));
        $queryWithoutMonth = request()->except(['month', 'page']);
        $datedProjects = $projects->filter(fn ($project) => $project->end_date);
        $eventsByDate = $datedProjects->groupBy(fn ($project) => $project->end_date->format('Y-m-d'));
        $monthEvents = $datedProjects->filter(fn ($project) => $project->end_date->isSameMonth($month));
        $startingThisMonth = $projects->filter(fn ($project) => $project->start_date && $project->start_date->isSameMonth($month));
        $upcomingProjects = $datedProjects
            ->filter(fn ($project) => $project->status !== 'completed' && $project->end_date->greaterThanOrEqualTo(now()->startOfDay()))
            ->sortBy('end_date')
            ->take(6);
        $overdueProjects = $datedProjects
            ->filter(fn ($project) => $project->status !== 'completed' && $project->end_date->isPast());
        $eventClass = [
            'pending' => 'calendar-event-pending',
            'in_progress' => 'calendar-event-progress',
            'completed' => 'calendar-event-completed',
        ];
        $stats = [
            ['label' => 'Deadlines', 'value' => $monthEvents->count(), 'meta' => $month->format('F Y')],
            ['label' => 'Starting', 'value' => $startingThisMonth->count(), 'meta' => 'Projects beginning'],
            ['label' => 'Overdue', 'value' => $overdueProjects->count(), 'meta' => 'Need follow-up'],
        ];
    @endphp

    <section class="space-y-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-2xl">
                <p class="kanban-eyebrow">Calendar view</p>
                <h2 class="kanban-title">Projects - Calendar</h2>
                <p class="kanban-subtitle">Track project deadlines by month and keep upcoming delivery dates visible.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('projects.calendar', array_merge($queryWithoutMonth, ['month' => $month->copy()->subMonth()->format('Y-m')])) }}"
                    class="btn-secondary">
                    <i class="ti ti-chevron-left"></i>
                    Prev
                </a>
                <a href="{{ route('projects.calendar', $queryWithoutMonth) }}" class="btn-secondary">
                    Today
                </a>
                <a href="{{ route('projects.calendar', array_merge($queryWithoutMonth, ['month' => $month->copy()->addMonth()->format('Y-m')])) }}"
                    class="btn-secondary">
                    Next
                    <i class="ti ti-chevron-right"></i>
                </a>
                <button type="button" class="btn-primary" data-modal-open="create-project-modal">
                    <i class="ti ti-plus"></i>
                    Add project
                </button>
            </div>
        </div>

        <div class="view-toolbar">
            <a href="{{ route('projects.index', $queryWithoutMonth) }}" class="btn-secondary"><i class="ti ti-layout-kanban mr-1"></i> Board</a>
            <a href="{{ route('projects.table', $queryWithoutMonth) }}" class="btn-secondary"><i class="ti ti-table mr-1"></i> Table</a>
            <a href="{{ route('projects.gantt', $queryWithoutMonth) }}" class="btn-secondary"><i class="ti ti-timeline mr-1"></i> Gantt</a>
            <span class="btn-secondary btn-secondary-active"><i class="ti ti-calendar mr-1"></i> Calendar</span>
            <span class="ml-auto text-[12px] text-[#888888]">{{ $datedProjects->count() }} dated projects</span>
        </div>

        <div class="grid gap-4 xl:grid-cols-[1fr_18rem]">
            <div class="space-y-4 min-w-0">
                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach ($stats as $stat)
                        <article class="metric-card">
                            <p class="metric-label">{{ $stat['label'] }}</p>
                            <p class="metric-value mt-3">{{ $stat['value'] }}</p>
                            <p class="mt-1 text-xs text-[var(--muted)]">{{ $stat['meta'] }}</p>
                        </article>
                    @endforeach
                </div>

                <div class="calendar-shell p-4">
                    <div class="calendar-month-bar">
                        <div>
                            <h3 class="font-['Syne'] text-lg font-bold text-[var(--text-strong)]">{{ $month->format('F Y') }}</h3>
                            <p class="text-[12.5px] text-[var(--muted)]">{{ $monthEvents->count() }} deadlines this month</p>
                        </div>
                        <span class="status-tag status-tag-pending">{{ now()->format('d M') }}</span>
                    </div>

                    <div class="calendar-grid">
                        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
                            <div class="calendar-head">{{ $dayName }}</div>
                        @endforeach

                        @foreach ($days as $day)
                            @php
                                $dateKey = $day->format('Y-m-d');
                                $events = $eventsByDate->get($dateKey, collect())->sortBy('end_date');
                            @endphp
                            <div class="calendar-day {{ $day->month !== $month->month ? 'calendar-day-muted' : '' }}"
                                data-calendar-create-date="{{ $dateKey }}"
                                data-calendar-create-label="{{ $day->format('d M Y') }}">
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <div class="calendar-day-number {{ $day->isToday() ? 'calendar-day-today' : '' }}">
                                        {{ $day->day }}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if ($events->isNotEmpty())
                                            <span class="text-[10.5px] font-semibold text-[var(--muted)]">{{ $events->count() }}</span>
                                        @endif
                                        <button type="button" class="calendar-day-add" aria-label="Add project on {{ $day->format('d M Y') }}"
                                            data-modal-open="create-project-modal"
                                            data-create-date="{{ $dateKey }}">
                                            <i class="ti ti-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    @foreach ($events->take(3) as $project)
                                        <a href="{{ route('projects.show', $project) }}"
                                            class="calendar-event {{ $eventClass[$project->status] ?? $eventClass['pending'] }}"
                                            title="{{ $project->name }}">
                                            <span class="calendar-event-dot"></span>
                                            <span class="truncate">{{ $project->name }}</span>
                                        </a>
                                    @endforeach
                                </div>

                                @if ($events->count() > 3)
                                    <div class="mt-1 text-[11px] text-[#888888]">+{{ $events->count() - 3 }} more</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <aside class="space-y-4">
                <article class="report-card p-4">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h3 class="font-['Syne'] text-sm font-bold text-[var(--text-strong)]">Upcoming</h3>
                        <span class="text-[11px] text-[var(--muted)]">Next deadlines</span>
                    </div>

                    <div class="space-y-3">
                        @forelse ($upcomingProjects as $project)
                            <a href="{{ route('projects.show', $project) }}" class="calendar-agenda-item">
                                <span class="calendar-agenda-date">{{ $project->end_date->format('d M') }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-[13px] font-medium text-[var(--text-strong)]">{{ $project->name }}</span>
                                    <span class="block truncate text-[11px] text-[var(--muted)]">{{ str($project->status)->replace('_', ' ')->title() }}</span>
                                </span>
                            </a>
                        @empty
                            <p class="rounded-md border border-[var(--line)] bg-[#f4f4f4] p-3 text-[12px] text-[var(--muted)]">
                                No upcoming deadlines.
                            </p>
                        @endforelse
                    </div>
                </article>

                <article class="report-card p-4">
                    <h3 class="font-['Syne'] text-sm font-bold text-[var(--text-strong)]">Status mix</h3>
                    <div class="mt-4 space-y-3">
                        @foreach (['pending' => 'Pending', 'in_progress' => 'In progress', 'completed' => 'Completed'] as $status => $label)
                            @php
                                $count = $monthEvents->where('status', $status)->count();
                                $percent = $monthEvents->count() ? round(($count / $monthEvents->count()) * 100) : 0;
                            @endphp
                            <div>
                                <div class="mb-1 flex items-center justify-between text-[12px]">
                                    <span class="text-[var(--muted)]">{{ $label }}</span>
                                    <span class="font-medium text-[var(--text-strong)]">{{ $count }}</span>
                                </div>
                                <div class="report-meter">
                                    <div class="report-meter-fill" style="width: {{ $percent }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            </aside>
        </div>
    </section>

    <div class="modal-shell {{ session('open_modal') === 'create-project-modal' ? '' : 'hidden' }}" id="create-project-modal"
        data-reset-on-open="true"
        data-modal tabindex="-1" aria-hidden="{{ session('open_modal') === 'create-project-modal' ? 'false' : 'true' }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-panel modal-panel-form">
            <div class="modal-header">
                <div>
                    <p class="modal-eyebrow">Calendar project</p>
                    <h2 class="modal-title">New project</h2>
                    <p class="modal-subtitle">Create a project for the selected calendar day.</p>
                </div>
                <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
            </div>

            <form action="{{ route('projects.store') }}" method="POST" class="space-y-5" autocomplete="off" spellcheck="false">
                @csrf
                <input type="text" tabindex="-1" autocomplete="username" class="hidden" aria-hidden="true">
                <input type="password" tabindex="-1" autocomplete="new-password" class="hidden" aria-hidden="true">

                @include('projects.partials.form', [
                    'project' => null,
                    'prefix' => 'create-project',
                    'errorBag' => 'createProject',
                    'useOldValues' => session('open_modal') === 'create-project-modal',
                    'disableAutofill' => true,
                    'namePrefix' => 'create_',
                ])

                <div class="modal-actions">
                    <button type="submit" class="btn-primary">Save project</button>
                    <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection
