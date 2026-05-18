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
                <a href="{{ route('projects.index') }}" class="topbar-chip {{ request()->routeIs('projects.index') ? 'topbar-chip-active' : '' }}">
                    <i class="ti ti-layout-kanban mr-1"></i> Board
                </a>
                <a href="{{ route('projects.table') }}" class="topbar-chip {{ request()->routeIs('projects.table') ? 'topbar-chip-active' : '' }}">
                    <i class="ti ti-table mr-1"></i> Table
                </a>
                <a href="{{ route('projects.gantt') }}" class="topbar-chip {{ request()->routeIs('projects.gantt') ? 'topbar-chip-active' : '' }}">
                    <i class="ti ti-timeline mr-1"></i> Gantt
                </a>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 lg:justify-between">
            <div class="flex flex-1 flex-wrap items-center gap-2">
                <form method="GET" action="{{ request()->routeIs('projects.table') ? route('projects.table') : route('projects.index') }}" class="relative min-w-[220px] flex-1 max-w-md">
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

                <button class="icon-button" type="button" aria-label="Notifications">
                    <i class="ti ti-bell text-base"></i>
                </button>
            </div>

            @if (request()->routeIs('projects.index'))
                <button type="button" class="btn-primary" data-modal-open="create-project-modal">
                    <i class="ti ti-plus text-base"></i>
                    <span>Add project</span>
                </button>
            @else
                <a href="{{ route('projects.create') }}" class="btn-primary">
                    <i class="ti ti-plus text-base"></i>
                    <span>Add project</span>
                </a>
            @endif
        </div>
    </div>
</nav>
