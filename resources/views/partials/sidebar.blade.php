@php
    $userInitials = strtoupper(substr(auth()->user()->name, 0, 2));
    $navItems = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'dashboard', 'active' => 'dashboard'],
        auth()->user()->isAdmin() ? ['label' => 'Users', 'icon' => 'users', 'route' => 'users.index', 'active' => 'users.*'] : null,
        ['label' => 'Board', 'icon' => 'grid', 'route' => 'projects.index', 'active' => ['projects.index', 'projects.create', 'projects.show', 'projects.edit']],
        ['label' => 'Tasks', 'icon' => 'checklist', 'route' => 'tasks.index', 'active' => 'tasks.*'],
        ['label' => 'Table', 'icon' => 'table', 'route' => 'projects.table', 'active' => 'projects.table'],
        ['label' => 'Gantt', 'icon' => 'chart', 'route' => 'projects.gantt', 'active' => 'projects.gantt'],
        ['label' => 'Calendar', 'icon' => 'calendar', 'route' => 'projects.calendar', 'active' => 'projects.calendar'],
        ['label' => 'Réunions', 'icon' => 'meeting', 'route' => 'meetings.index', 'active' => 'meetings.*'],
        ['label' => 'Reports', 'icon' => 'report', 'route' => 'projects.reports', 'active' => 'projects.reports'],
    ];
    $navItems = array_filter($navItems);
@endphp

<aside id="sidebar"
    class="workspace-sidebar fixed inset-y-0 left-0 z-40 flex w-[230px] -translate-x-full flex-col overflow-hidden lg:sticky lg:top-0 lg:translate-x-0">
    <div class="shrink-0 border-b border-black/10 px-3 py-5">
        <a href="{{ route('dashboard') }}" class="block">
            <img src="{{ asset('images/dailyops-logo.svg') }}" alt="DailyOps" class="h-auto w-full">
        </a>
    </div>

    <div class="shrink-0 px-3">
        <div class="workspace-switcher">
            <div
                class="flex h-8 w-8 items-center justify-center rounded-md border border-[#c50064]/20 bg-[#c50064]/10 text-xs font-semibold text-[#c50064]">
                DO
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-[12.5px] font-medium text-[var(--text-strong)]">DailyOps</p>
                <p class="truncate text-[10.5px] text-[var(--muted)]">{{ auth()->user()->isAdmin() ? 'Admin workspace' : 'Member workspace' }}</p>
            </div>
            <i class="ti ti-chevron-down text-[13px] text-[var(--muted)]"></i>
        </div>
    </div>

    <div class="custom-scroll min-h-0 flex-1 space-y-6 overflow-y-auto px-2 py-4">
        <section>
            <p class="sidebar-section-title">Navigation</p>
            <nav class="mt-3 space-y-1.5">
                @foreach ($navItems as $item)
                    @php
                        $activePatterns = (array) $item['active'];
                        $isActive = request()->routeIs(...$activePatterns);
                        $href = $item['route'] ? route($item['route']) : '#';
                    @endphp
                    <a href="{{ $href }}" class="sidebar-link {{ $isActive ? 'active-link' : '' }}" @if ($isActive) aria-current="page" @endif>
                        <span class="sidebar-icon">
                            @if ($item['icon'] === 'dashboard')
                                <i class="ti ti-layout-dashboard"></i>
                            @elseif ($item['icon'] === 'users')
                                <i class="ti ti-users"></i>
                            @elseif ($item['icon'] === 'grid')
                                <i class="ti ti-layout-dashboard"></i>
                            @elseif ($item['icon'] === 'table')
                                <i class="ti ti-table"></i>
                            @elseif ($item['icon'] === 'checklist')
                                <i class="ti ti-checklist"></i>
                            @elseif ($item['icon'] === 'chart')
                                <i class="ti ti-chart-bar"></i>
                            @elseif ($item['icon'] === 'calendar')
                                <i class="ti ti-calendar"></i>
                            @elseif ($item['icon'] === 'meeting')
                                <i class="ti ti-video"></i>
                            @else
                                <i class="ti ti-report"></i>
                            @endif
                        </span>
                        <span class="flex-1 truncate {{ $isActive ? 'font-semibold' : 'font-normal' }}">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </section>
    </div>

    <div class="shrink-0 border-t border-[var(--line)] p-4">
        <div class="flex items-center gap-3">
            <div
                class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-[#c50064] to-[#850044] font-['Syne'] text-xs font-semibold text-white shadow-[0_0_12px_rgba(197,0,100,0.3)]">
                {{ $userInitials }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-[var(--text-strong)]">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-[var(--muted)]">{{ auth()->user()->role }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="icon-button h-8 w-8 p-0" type="submit" aria-label="Log out">
                    <i class="ti ti-logout text-base"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
