@extends('layouts.app')

@section('content')
    @php
        $navigationQuery = array_filter(request()->only(['search', 'status']), fn ($value) => filled($value));
        $month = now()->startOfMonth();
        $calendarStart = $month->copy()->startOfWeek();
        $days = collect(range(0, 41))->map(fn ($day) => $calendarStart->copy()->addDays($day));
        $eventsByDate = $projects
            ->filter(fn ($project) => $project->end_date)
            ->groupBy(fn ($project) => $project->end_date->format('Y-m-d'));
        $eventClass = [
            'pending' => 'bg-[#e8007d]/10 text-[#e8007d]',
            'in_progress' => 'bg-[#d97706]/10 text-[#d97706]',
            'completed' => 'bg-[#00a86b]/10 text-[#00a86b]',
        ];
    @endphp

    <section>
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="h-2 w-2 rounded-full bg-[#e8007d] shadow-[0_0_8px_rgba(232,0,125,0.5)]"></span>
                <div>
                    <h2 class="font-['Syne'] text-base font-bold text-[#0a0a0a]">Projects - Calendar</h2>
                    <p class="mt-1 text-[12.5px] text-[#888888]">{{ $month->format('F Y') }} deadlines.</p>
                </div>
            </div>
            <a href="{{ route('projects.create') }}" class="btn-primary">
                <i class="ti ti-plus"></i>
                Add project
            </a>
        </div>

        <div class="view-toolbar">
            <a href="{{ route('projects.table', $navigationQuery) }}" class="btn-secondary"><i class="ti ti-table mr-1"></i> Table</a>
            <a href="{{ route('projects.gantt', $navigationQuery) }}" class="btn-secondary"><i class="ti ti-timeline mr-1"></i> Gantt</a>
            <span class="ml-auto text-[12px] text-[#888888]">{{ $projects->filter(fn ($project) => $project->end_date)->count() }} dated projects</span>
        </div>

        <div class="calendar-shell p-4">
            <div class="calendar-grid">
                @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
                    <div class="calendar-head">{{ $dayName }}</div>
                @endforeach

                @foreach ($days as $day)
                    @php
                        $dateKey = $day->format('Y-m-d');
                        $events = $eventsByDate->get($dateKey, collect());
                    @endphp
                    <div class="calendar-day {{ $day->month !== $month->month ? 'calendar-day-muted' : '' }}">
                        <div class="calendar-day-number {{ $day->isToday() ? 'calendar-day-today' : '' }}">
                            {{ $day->day }}
                        </div>
                        @foreach ($events->take(3) as $project)
                            <a href="{{ route('projects.show', $project) }}" class="calendar-event {{ $eventClass[$project->status] ?? $eventClass['pending'] }}">
                                {{ $project->name }}
                            </a>
                        @endforeach
                        @if ($events->count() > 3)
                            <div class="text-[11px] text-[#888888]">+{{ $events->count() - 3 }} more</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
