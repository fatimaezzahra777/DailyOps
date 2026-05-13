<nav class="sticky top-0 z-20 border-b border-[var(--line)] bg-[var(--panel)]/85 backdrop-blur-xl">
    <div class="flex flex-col gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-3">
                <button id="menu-btn" class="icon-button lg:hidden" type="button" aria-label="Open menu">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
                    </svg>
                </button>

                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--muted)]">Workspace</p>
                    <h1 class="truncate text-lg font-semibold text-[var(--text-strong)] sm:text-xl">PH Marketing</h1>
                </div>
            </div>

            <div class="hidden items-center gap-2 lg:flex">
                <button class="topbar-chip topbar-chip-active" type="button">Board</button>
                <button class="topbar-chip" type="button">Table</button>
                <button class="topbar-chip" type="button">Gantt</button>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 lg:justify-between">
            <div class="flex flex-1 flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('projects.index') }}" class="relative min-w-[220px] flex-1 max-w-md">
                    @if (request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--muted)]"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5" stroke-linecap="round"></path>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks, projects..."
                        class="w-full pl-10 pr-4">
                </form>

                <button class="icon-button" type="button" aria-label="Filter">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 6h16M7 12h10M10 18h4" stroke-linecap="round" />
                    </svg>
                </button>

                <button id="theme-toggle" class="icon-button gap-2 px-3" type="button" aria-pressed="false">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path
                            d="M12 3v2.25M12 18.75V21M4.97 4.97l1.59 1.59M17.44 17.44l1.59 1.59M3 12h2.25M18.75 12H21M4.97 19.03l1.59-1.59M17.44 6.56l1.59-1.59"
                            stroke-linecap="round" />
                        <circle cx="12" cy="12" r="4"></circle>
                    </svg>
                    <span data-theme-label>Dark</span>
                </button>
            </div>

            <a href="{{ route('projects.create') }}" class="btn-primary">
                <span class="text-base leading-none">+</span>
                <span>Add task</span>
            </a>
        </div>
    </div>
</nav>
