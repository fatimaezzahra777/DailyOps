@extends('layouts.app')

@section('content')
    <section class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="kanban-eyebrow">Meetings workspace</p>
                <h1 class="kanban-title">All meetings</h1>
                <p class="kanban-subtitle">View meetings you organize or participate in.</p>
            </div>

        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <article class="metric-card">
                <p class="metric-label">Upcoming</p>
                <p class="metric-value mt-3">{{ $upcomingCount }}</p>
                <p class="mt-1 text-xs text-[var(--muted)]">Scheduled meetings</p>
            </article>
            <article class="metric-card">
                <p class="metric-label">Organized</p>
                <p class="metric-value mt-3">{{ $organizedCount }}</p>
                <p class="mt-1 text-xs text-[var(--muted)]">Created by you</p>
            </article>
            <article class="metric-card">
                <p class="metric-label">Completeds</p>
                <p class="metric-value mt-3">{{ $pastCount }}</p>
                <p class="mt-1 text-xs text-[var(--muted)]">Past meetings</p>
            </article>
        </div>

        <form action="{{ route('meetings.index') }}" method="GET" class="meetings-search">
            <i class="ti ti-search text-lg text-[var(--muted)]"></i>
            <input name="search" type="search" value="{{ request('search') }}"
                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm shadow-none focus:border-0 focus:ring-0"
                placeholder="Search par nom, titre ou organisateur...">
            @if (request()->filled('search'))
                <a href="{{ route('meetings.index') }}" class="meetings-search-clear" aria-label="Reset search">
                    <i class="ti ti-x"></i>
                </a>
            @endif
            <button type="submit" class="meetings-search-submit" aria-label="Search">
                <i class="ti ti-arrow-right"></i>
            </button>
        </form>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @forelse ($meetings as $meeting)
                @php
                    $isOrganizer = $meeting->isOrganizedBy(auth()->user());
                    $isPast = $meeting->scheduled_at->isPast();
                    $isToday = $meeting->scheduled_at->isToday();
                    $meetingDate = $meeting->scheduled_at->copy()->locale('fr');
                    $visibleParticipants = $meeting->participants->take(3);
                    $remainingParticipants = max(0, $meeting->participants->count() - $visibleParticipants->count());
                    $statusLabel = $isPast ? 'Completed' : ($isToday ? "Today" : 'Upcoming');
                    $statusClass = $isPast ? 'meeting-card-status-past' : ($isToday ? 'meeting-card-status-today' : 'meeting-card-status-upcoming');
                @endphp

                <article class="meeting-card-simple {{ $isPast ? 'meeting-card-simple-past' : '' }}"
                    style="--meeting-card-delay: {{ min($loop->index * 55, 330) }}ms;">
                    <span class="meeting-card-side {{ $isToday ? 'meeting-card-side-today' : '' }}"></span>

                    <div class="meeting-card-simple-content flex items-start justify-between gap-3">
                        <span class="meeting-card-status {{ $statusClass }}">
                            @if ($isToday)
                                <span class="meeting-card-live-dot"></span>
                            @endif
                            {{ $statusLabel }}
                        </span>

                        <a href="{{ route('projects.calendar', ['month' => $meeting->scheduled_at->format('Y-m')]) }}"
                            class="meeting-card-menu" aria-label="View details in calendar"
                            title="View in calendar">
                            <i class="ti ti-dots-vertical"></i>
                        </a>
                    </div>

                    <div class="meeting-card-simple-content mt-5 min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--muted)]">
                            {{ $isOrganizer ? 'You organize' : 'You participate' }}
                        </p>
                        <h2 class="mt-1.5 truncate font-['Syne'] text-[21px] font-semibold leading-tight text-[#242424]"
                            title="{{ $meeting->title }}">
                            {{ $meeting->title }}
                        </h2>
                        <p class="mt-1 truncate text-sm text-[var(--muted)]">{{ $meeting->name }}</p>
                    </div>

                    <dl class="meeting-card-simple-content mt-4 space-y-3 text-[12.5px] font-medium text-[#555555]">
                        <div class="flex items-center gap-2.5">
                            <i class="ti ti-clock text-base"></i>
                            <span>
                                {{ $meetingDate->translatedFormat('D d M') }} · {{ $meeting->scheduled_at->format('H:i') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <i class="ti ti-user text-base"></i>
                            <span class="truncate">{{ $meeting->organizer->name }}</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <i class="ti ti-video text-base"></i>
                            <span>Meeting en ligne</span>
                        </div>
                    </dl>

                    <div class="meeting-card-simple-footer mt-4 border-t border-[#eeeeee] pt-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex -space-x-2">
                                @foreach ($visibleParticipants as $participant)
                                    <span class="meeting-avatar-simple" title="{{ $participant->name }}">
                                        {{ strtoupper(substr($participant->name, 0, 2)) }}
                                    </span>
                                @endforeach
                                @if ($remainingParticipants > 0)
                                    <span class="meeting-avatar-simple meeting-avatar-simple-more">+{{ $remainingParticipants }}</span>
                                @endif
                            </div>

                            @if ($isPast)
                                <a href="{{ route('projects.calendar', ['month' => $meeting->scheduled_at->format('Y-m')]) }}"
                                    class="meeting-details-button">
                                    Details
                                </a>
                            @else
                                <a href="{{ $meeting->meeting_url }}" target="_blank" rel="noopener noreferrer"
                                    class="meeting-join-button-simple">
                                    Join
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="meetings-empty-simple md:col-span-2 xl:col-span-3 2xl:col-span-4">
                    <span class="meetings-empty-simple-icon"><i class="ti ti-calendar-off"></i></span>
                    <h2 class="mt-4 font-['Syne'] text-xl font-bold text-[var(--text-strong)]">No meeting found</h2>
                    <p class="mt-2 text-sm text-[var(--muted)]">Schedule a meeting from the calendar to see it here.</p>
                </div>
            @endforelse
        </div>

        @if ($meetings->hasPages())
            <div>{{ $meetings->links() }}</div>
        @endif
    </section>
@endsection
