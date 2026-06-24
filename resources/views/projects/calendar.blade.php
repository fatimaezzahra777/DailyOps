@extends('layouts.app')

@section('content')
    @php
        $navigationQuery = array_filter(request()->only(['search', 'status']), fn ($value) => filled($value));
        $month = $month->copy()->startOfMonth();
        $calendarStart = $month->copy()->startOfWeek();
        $days = collect(range(0, 41))->map(fn ($day) => $calendarStart->copy()->addDays($day));
        $queryWithoutMonth = request()->except(['month', 'page']);
        $datedProjects = $projects->filter(fn ($project) => $project->end_date);
        $eventsByDate = $datedProjects->groupBy(fn ($project) => $project->end_date->format('Y-m-d'));
        $meetingsByDate = $meetings->groupBy(fn ($meeting) => $meeting->scheduled_at->format('Y-m-d'));
        $monthEvents = $datedProjects->filter(fn ($project) => $project->end_date->isSameMonth($month));
        $monthMeetings = $meetings->filter(fn ($meeting) => $meeting->scheduled_at->isSameMonth($month));
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
            'testing' => 'calendar-event-testing',
            'completed' => 'calendar-event-completed',
        ];
        $stats = [
            ['label' => 'Échéances', 'value' => $monthEvents->count(), 'meta' => $month->format('F Y')],
            ['label' => 'Réunions', 'value' => $monthMeetings->count(), 'meta' => 'Planifiées ce mois-ci'],
            ['label' => 'Démarrages', 'value' => $startingThisMonth->count(), 'meta' => 'Projets démarrant ce mois'],
            ['label' => 'En retard', 'value' => $overdueProjects->count(), 'meta' => 'Suivi nécessaire'],
        ];
        $openModal = session('open_modal');
        $selectedEventType = session('calendar_event_type', 'project');
    @endphp

    <section class="space-y-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-2xl">
                <p class="kanban-eyebrow">Vue calendrier</p>
                <h2 class="kanban-title">Projets - Calendrier</h2>
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

            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-[1fr_18rem]">
            <div class="space-y-4 min-w-0">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
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
                                $projectEvents = $eventsByDate->get($dateKey, collect())->map(fn ($project) => [
                                    'type' => 'project',
                                    'sort_at' => $project->end_date->copy()->startOfDay(),
                                    'model' => $project,
                                ]);
                                $meetingEvents = $meetingsByDate->get($dateKey, collect())->map(fn ($meeting) => [
                                    'type' => 'meeting',
                                    'sort_at' => $meeting->scheduled_at,
                                    'model' => $meeting,
                                ]);
                                $events = $projectEvents->concat($meetingEvents)->sortBy('sort_at')->values();
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
                                        <button type="button" class="calendar-day-add" aria-label="Ajouter un événement le {{ $day->format('d M Y') }}"
                                            data-modal-open="create-project-modal"
                                            data-create-date="{{ $dateKey }}">
                                            <i class="ti ti-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    @foreach ($events->take(3) as $event)
                                        @if ($event['type'] === 'meeting')
                                            @php
                                                $meeting = $event['model'];
                                            @endphp
                                            <button type="button" data-modal-open="meeting-details-modal-{{ $meeting->id }}"
                                                class="calendar-event calendar-event-meeting"
                                                title="{{ $meeting->title }} — {{ $meeting->scheduled_at->format('H:i') }}">
                                                <span class="calendar-event-dot"></span>
                                                <span class="truncate">{{ $meeting->scheduled_at->format('H:i') }} · {{ $meeting->title }}</span>
                                            </button>
                                        @else
                                            @php
                                                $project = $event['model'];
                                            @endphp
                                            <a href="{{ route('projects.show', $project) }}"
                                                class="calendar-event {{ $eventClass[$project->status] ?? $eventClass['pending'] }}"
                                                title="{{ $project->name }}">
                                                <span class="calendar-event-dot"></span>
                                                <span class="truncate">{{ $project->name }}</span>
                                            </a>
                                        @endif
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
                                    <span class="block truncate text-[11px] text-[var(--muted)]">{{ \App\Models\Project::statusLabel($project->status) }}</span>
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
                    <h3 class="font-['Syne'] text-sm font-bold text-[var(--text-strong)]">Répartition des statuts</h3>
                    <div class="mt-4 space-y-3">
                        @foreach (\App\Models\Project::statusOptions() as $status => $label)
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

    <div class="modal-shell {{ $openModal === 'create-project-modal' ? '' : 'hidden' }}" id="create-project-modal"
        data-reset-on-open="true"
        data-calendar-event-modal
        data-modal tabindex="-1" aria-hidden="{{ $openModal === 'create-project-modal' ? 'false' : 'true' }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-panel modal-panel-form">
            <div class="modal-header">
                <div>
                    <p class="modal-eyebrow">Événement calendrier</p>
                    <h2 class="modal-title">Nouvel événement</h2>
                    <p class="modal-subtitle">Créez un projet ou planifiez une réunion pour la date sélectionnée.</p>
                </div>
                <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
            </div>

            <div class="mb-6 grid grid-cols-2 gap-1 rounded-full bg-[#efe2cd] p-1.5" data-calendar-event-selector>
                <button type="button" class="rounded-full px-4 py-3 text-sm font-bold transition"
                    data-calendar-event-type="project"
                    data-selected="{{ $selectedEventType === 'project' ? 'true' : 'false' }}">
                    Projet
                </button>
                <button type="button" class="rounded-full px-4 py-3 text-sm font-bold transition"
                    data-calendar-event-type="meeting"
                    data-selected="{{ $selectedEventType === 'meeting' ? 'true' : 'false' }}">
                    Réunion
                </button>
            </div>

            <div data-calendar-event-form="project" class="{{ $selectedEventType === 'project' ? '' : 'hidden' }}">
                <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5" autocomplete="off" spellcheck="false">
                    @csrf
                    <input type="text" tabindex="-1" autocomplete="username" class="hidden" aria-hidden="true">
                    <input type="password" tabindex="-1" autocomplete="new-password" class="hidden" aria-hidden="true">

                    @include('projects.partials.form', [
                        'project' => null,
                        'prefix' => 'create-project',
                        'errorBag' => 'createProject',
                        'useOldValues' => $openModal === 'create-project-modal' && $selectedEventType === 'project',
                        'disableAutofill' => true,
                        'namePrefix' => 'create_',
                    ])

                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Enregistrer le projet</button>
                        <button type="button" class="btn-secondary" data-modal-close>Annuler</button>
                    </div>
                </form>
            </div>

            <div data-calendar-event-form="meeting" class="{{ $selectedEventType === 'meeting' ? '' : 'hidden' }}">
                <form action="{{ route('meetings.store') }}" method="POST" class="space-y-5" autocomplete="off">
                    @csrf

                    @if ($errors->getBag('createMeeting')->any())
                        <div class="rounded-md border border-red-600/20 bg-red-600/10 p-4 text-sm text-red-600">
                            <p class="font-medium text-red-700">Veuillez corriger les erreurs suivantes :</p>
                            <ul class="mt-2 space-y-1">
                                @foreach ($errors->getBag('createMeeting')->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @include('meetings._form', [
                        'meeting' => null,
                        'prefix' => 'create-meeting',
                        'useOldValues' => $selectedEventType === 'meeting',
                    ])

                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Créer la réunion</button>
                        <button type="button" class="btn-secondary" data-modal-close>Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach ($meetings as $meeting)
        @php
            $detailModalId = "meeting-details-modal-{$meeting->id}";
            $editModalId = "edit-meeting-modal-{$meeting->id}";
            $deleteModalId = "delete-meeting-modal-{$meeting->id}";
            $editErrorBag = "updateMeeting.{$meeting->id}";
            $canManageMeeting = $meeting->isOrganizedBy(auth()->user());
        @endphp

        <div class="modal-shell {{ $openModal === $detailModalId ? '' : 'hidden' }}" id="{{ $detailModalId }}"
            data-modal tabindex="-1" aria-hidden="{{ $openModal === $detailModalId ? 'false' : 'true' }}">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-panel">
                <div class="modal-header">
                    <div>
                        <p class="modal-eyebrow">Réunion</p>
                        <h2 class="modal-title">{{ $meeting->title }}</h2>
                        <p class="modal-subtitle">{{ $meeting->name }}</p>
                    </div>
                    <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
                </div>

                <div class="grid gap-6 lg:grid-cols-[1fr_0.8fr]">
                    <article class="panel-dark p-5">
                        <dl class="space-y-4 text-sm">
                            <div>
                                <dt class="text-[var(--muted)]">Date et heure</dt>
                                <dd class="mt-1 font-semibold text-[var(--text-strong)]">
                                    {{ $meeting->scheduled_at->format('d M Y à H:i') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-[var(--muted)]">Organisateur</dt>
                                <dd class="mt-1 font-semibold text-[var(--text-strong)]">{{ $meeting->organizer->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-[var(--muted)]">Lien</dt>
                                <dd class="mt-1 break-all text-[var(--text-strong)]">{{ $meeting->meeting_url }}</dd>
                            </div>
                        </dl>
                    </article>

                    <aside class="panel-dark p-5">
                        <h3 class="text-sm font-semibold text-[var(--text-strong)]">
                            Participants ({{ $meeting->participants->count() }})
                        </h3>
                        <div class="mt-4 space-y-2">
                            @foreach ($meeting->participants as $participant)
                                <div class="rounded-md border border-[var(--line)] bg-white px-3 py-2">
                                    <p class="text-sm font-medium text-[var(--text-strong)]">{{ $participant->name }}</p>
                                    <p class="text-xs text-[var(--muted)]">{{ $participant->email }}</p>
                                </div>
                            @endforeach
                        </div>
                    </aside>
                </div>

                <div class="modal-actions">
                    <a href="{{ $meeting->meeting_url }}" target="_blank" rel="noopener noreferrer" class="btn-primary">
                        Rejoindre la réunion
                    </a>
                    @if ($canManageMeeting)
                        <button type="button" class="icon-button h-10 w-10 p-0" data-modal-switch="{{ $editModalId }}"
                            aria-label="Modifier la réunion" title="Modifier la réunion">
                            <span class="material-symbols-rounded text-[20px]">edit</span>
                        </button>
                        <button type="button" class="icon-button h-10 w-10 p-0" data-modal-switch="{{ $deleteModalId }}"
                            aria-label="Supprimer la réunion" title="Supprimer la réunion">
                            <span class="material-symbols-rounded text-[20px]">delete</span>
                        </button>
                    @endif
                    <button type="button" class="btn-secondary" data-modal-close>Fermer</button>
                </div>
            </div>
        </div>

        @if ($canManageMeeting)
            <div class="modal-shell {{ $openModal === $editModalId ? '' : 'hidden' }}" id="{{ $editModalId }}"
                data-modal tabindex="-1" aria-hidden="{{ $openModal === $editModalId ? 'false' : 'true' }}">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-panel modal-panel-form">
                    <div class="modal-header">
                        <div>
                            <p class="modal-eyebrow">Modifier la réunion</p>
                            <h2 class="modal-title">{{ $meeting->title }}</h2>
                            <p class="modal-subtitle">Mettez à jour les informations et les participants.</p>
                        </div>
                        <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
                    </div>

                    <form action="{{ route('meetings.update', $meeting) }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')

                        @if ($errors->getBag($editErrorBag)->any())
                            <div class="rounded-md border border-red-600/20 bg-red-600/10 p-4 text-sm text-red-600">
                                <ul class="space-y-1">
                                    @foreach ($errors->getBag($editErrorBag)->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @include('meetings._form', [
                            'meeting' => $meeting,
                            'prefix' => "edit-meeting-{$meeting->id}",
                            'useOldValues' => $openModal === $editModalId,
                        ])

                        <div class="modal-actions">
                            <button type="submit" class="btn-primary">Enregistrer</button>
                            <button type="button" class="btn-secondary" data-modal-close>Annuler</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal-shell {{ $openModal === $deleteModalId ? '' : 'hidden' }}" id="{{ $deleteModalId }}"
                data-modal tabindex="-1" aria-hidden="{{ $openModal === $deleteModalId ? 'false' : 'true' }}">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-panel modal-panel-compact">
                    <div class="modal-header">
                        <div>
                            <p class="modal-eyebrow text-red-600">Supprimer la réunion</p>
                            <h2 class="modal-title">{{ $meeting->title }}</h2>
                            <p class="modal-subtitle">Cette action est définitive.</p>
                        </div>
                        <button type="button" class="modal-close" data-modal-close aria-label="Close modal">×</button>
                    </div>

                    <div class="rounded-md border border-red-600/15 bg-red-600/10 p-4 text-sm text-[var(--text)]">
                        La réunion sera supprimée du calendrier de tous les participants.
                    </div>

                    <form action="{{ route('meetings.destroy', $meeting) }}" method="POST" class="modal-actions">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-primary">Supprimer</button>
                        <button type="button" class="btn-secondary" data-modal-close>Annuler</button>
                    </form>
                </div>
            </div>
        @endif
    @endforeach
@endsection
