@php
    $projectSearchRoute = match (true) {
        request()->routeIs('projects.table') => 'projects.table',
        request()->routeIs('projects.gantt') => 'projects.gantt',
        request()->routeIs('projects.calendar') => 'projects.calendar',
        request()->routeIs('projects.reports') => 'projects.reports',
        default => 'projects.index',
    };
    $projectNavigationQuery = request()->routeIs('projects.*')
        ? array_filter(request()->only(['search', 'status']), fn ($value) => filled($value))
        : [];
@endphp

<nav class="z-20 border-b border-[var(--line)] bg-white shadow-[0_1px_8px_rgba(0,0,0,0.06)]">
    <div class="flex flex-col gap-3 px-4 py-3 sm:px-5">
        <div class="flex items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-3">
                <button id="menu-btn" class="icon-button lg:hidden" type="button" aria-label="Open menu">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
                    </svg>
                </button>

                <div>
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-[#e8007d] shadow-[0_0_8px_rgba(232,0,125,0.5)]"></span>
                        <h1 class="truncate font-['Syne'] text-base font-bold text-[var(--text-strong)]">PH Marketing</h1>
                    </div>
                    <p class="mt-1 text-[12.5px] text-[var(--muted)]">Projects workspace</p>
                </div>
            </div>

            <div class="hidden items-center gap-2 lg:flex">
                <a href="{{ route('projects.index', $projectNavigationQuery) }}" class="topbar-chip {{ request()->routeIs('projects.index') ? 'topbar-chip-active' : '' }}">
                    <i class="ti ti-layout-kanban mr-1"></i> Board
                </a>
                <a href="{{ route('projects.table', $projectNavigationQuery) }}" class="topbar-chip {{ request()->routeIs('projects.table') ? 'topbar-chip-active' : '' }}">
                    <i class="ti ti-table mr-1"></i> Table
                </a>
                <a href="{{ route('projects.gantt', $projectNavigationQuery) }}" class="topbar-chip {{ request()->routeIs('projects.gantt') ? 'topbar-chip-active' : '' }}">
                    <i class="ti ti-timeline mr-1"></i> Gantt
                </a>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 lg:justify-between">
            <div class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
                <form method="GET" action="{{ route($projectSearchRoute) }}" class="relative min-w-0 flex-[1_1_100%] sm:flex-[1_1_220px] max-w-md">
                    @if (request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--muted)]"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5" stroke-linecap="round"></path>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search projects..."
                        class="w-full px-4 py-2 pl-10 text-[13px]">
                </form>

                <button class="icon-button" type="button" aria-label="Filter">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 6h16M7 12h10M10 18h4" stroke-linecap="round" />
                    </svg>
                </button>

                @auth
                    <div class="relative" data-realtime-notifications data-notification-user-id="{{ auth()->id() }}">
                        <button class="icon-button relative" type="button" aria-label="Notifications" data-notification-toggle>
                            <span class="material-symbols-rounded text-[22px] leading-none" aria-hidden="true">notifications</span>
                            <span class="absolute -right-1 -top-1 hidden min-w-5 rounded-full bg-[var(--accent)] px-1.5 py-0.5 text-[10px] font-bold leading-none text-white" data-notification-badge>0</span>
                        </button>

                        <div class="absolute right-0 top-11 z-40 hidden w-80 overflow-hidden rounded-md border border-[var(--line)] bg-white shadow-[0_16px_40px_rgba(0,0,0,0.12)]" data-notification-panel>
                            <div class="flex items-center gap-2 border-b border-[var(--line)] px-4 py-3">
                                <span class="material-symbols-rounded text-[20px] text-[var(--accent)]" aria-hidden="true">notifications</span>
                                <p class="text-sm font-semibold text-[var(--text-strong)]">Notifications</p>
                            </div>
                            <div class="max-h-80 overflow-y-auto" data-notification-list>
                                <div class="flex items-center gap-3 px-4 py-5 text-sm text-[var(--muted)]" data-notification-empty>
                                    <span class="material-symbols-rounded text-[22px]" aria-hidden="true">notifications_off</span>
                                    <span>Aucune nouvelle notification.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>

            @if (request()->routeIs('projects.index'))
                <button type="button" class="btn-primary w-full sm:w-auto" data-modal-open="create-project-modal">
                    <i class="ti ti-plus text-base"></i>
                    <span>Add project</span>
                </button>
            @else
                <a href="{{ route('projects.create') }}" class="btn-primary w-full sm:w-auto">
                    <i class="ti ti-plus text-base"></i>
                    <span>Add project</span>
                </a>
            @endif
        </div>
    </div>
</nav>

@auth
    <div id="notification-toast-container" class="fixed right-4 top-4 z-50 space-y-3"></div>
@endauth
