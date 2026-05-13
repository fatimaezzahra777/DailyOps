@php
    $navItems = [
        ['label' => 'Board', 'icon' => 'grid', 'route' => 'projects.index'],
        ['label' => 'Table', 'icon' => 'table', 'route' => null],
        ['label' => 'Gantt', 'icon' => 'chart', 'route' => null],
        ['label' => 'Calendar', 'icon' => 'calendar', 'route' => null],
        ['label' => 'Reports', 'icon' => 'report', 'route' => null],
    ];

    $workspaceItems = ['Notes', 'Files', 'Time tracking', 'Team'];
    $activeRoute = request()->route()?->getName();
@endphp

<aside id="sidebar"
    class="workspace-sidebar fixed inset-y-0 left-0 z-40 flex w-[280px] -translate-x-full flex-col lg:sticky lg:translate-x-0">
    <div class="flex items-center gap-3 px-5 py-5">
        <div
            class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-sm font-semibold text-white shadow-lg shadow-violet-500/30">
            ⚡
        </div>
        <div>
            <h1 class="text-sm font-semibold text-[var(--text-strong)]">DailyOps</h1>
            <p class="text-xs text-[var(--muted)]">Team workspace</p>
        </div>
    </div>

    <div class="px-4">
        <div class="workspace-switcher">
            <div
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/14 text-sm font-semibold text-violet-300 ring-1 ring-violet-500/20">
                PH
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-[var(--text-strong)]">PH Marketing</p>
                <p class="truncate text-xs text-[var(--muted)]">Admin workspace</p>
            </div>
            <svg class="h-4 w-4 text-[var(--muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
    </div>

    <div class="custom-scroll flex-1 space-y-8 overflow-y-auto px-4 py-6">
        <section>
            <p class="sidebar-section-title">Navigation</p>
            <nav class="mt-3 space-y-1.5">
                @foreach ($navItems as $item)
                    @php
                        $isActive = $item['route'] && $activeRoute === $item['route'];
                        $href = $item['route'] ? route($item['route']) : '#';
                    @endphp
                    <a href="{{ $href }}" class="sidebar-link {{ $isActive ? 'active-link' : '' }}">
                        <span class="sidebar-icon">
                            @if ($item['icon'] === 'grid')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z" />
                                </svg>
                            @elseif ($item['icon'] === 'table')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 6h16M4 12h16M4 18h16M8 4v16M16 4v16" stroke-linecap="round" />
                                </svg>
                            @elseif ($item['icon'] === 'chart')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 18 10 12l4 3 6-8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            @elseif ($item['icon'] === 'calendar')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M8 3v3M16 3v3M4 9h16M5 6h14a1 1 0 0 1 1 1v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a1 1 0 0 1 1-1Z" />
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M5 19V5l14 7Z" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            @endif
                        </span>
                        <span class="flex-1">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </section>

        <section>
            <p class="sidebar-section-title">Workspace</p>
            <nav class="mt-3 space-y-1.5">
                @foreach ($workspaceItems as $item)
                    <a href="#" class="sidebar-link">
                        <span class="sidebar-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M6 5h12v14H6z" />
                            </svg>
                        </span>
                        <span class="flex-1">{{ $item }}</span>
                    </a>
                @endforeach
            </nav>
        </section>
    </div>

    <div class="border-t border-[var(--line)] p-4">
        <div class="flex items-center gap-3 rounded-2xl bg-[var(--card-soft)] p-3">
            <div
                class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-500 text-xs font-semibold text-white">
                AM
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-[var(--text-strong)]">Alex Martin</p>
                <p class="truncate text-xs text-[var(--muted)]">Pro plan</p>
            </div>
            <button class="icon-button h-8 w-8 p-0" type="button" aria-label="Settings">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 15.5A3.5 3.5 0 1 0 12 8.5a3.5 3.5 0 0 0 0 7Z" />
                    <path
                        d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1.04 1.55V21a2 2 0 0 1-4 0v-.09a1.7 1.7 0 0 0-1.04-1.55 1.7 1.7 0 0 0-1.87.34l-.06.06A2 2 0 0 1 4.2 16.93l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.55-1.04H3a2 2 0 0 1 0-4h.09A1.7 1.7 0 0 0 4.64 8.9a1.7 1.7 0 0 0-.34-1.87l-.06-.06A2 2 0 0 1 7.07 4.14l.06.06A1.7 1.7 0 0 0 9 4.54 1.7 1.7 0 0 0 10.04 3H10a2 2 0 0 1 4 0v.09A1.7 1.7 0 0 0 15.04 4.64a1.7 1.7 0 0 0 1.87-.34l.06-.06A2 2 0 0 1 19.8 7.07l-.06.06A1.7 1.7 0 0 0 19.4 9a1.7 1.7 0 0 0 1.55 1.04H21a2 2 0 0 1 0 4h-.09A1.7 1.7 0 0 0 19.4 15Z"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
    </div>
</aside>
