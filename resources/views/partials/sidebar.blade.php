@php
    $navItems = [
        ['label' => 'Board', 'icon' => 'grid', 'route' => 'projects.index'],
        ['label' => 'Table', 'icon' => 'table', 'route' => 'projects.table'],
        ['label' => 'Gantt', 'icon' => 'chart', 'route' => 'projects.gantt'],
        ['label' => 'Calendar', 'icon' => 'calendar', 'route' => 'projects.calendar'],
        ['label' => 'Reports', 'icon' => 'report', 'route' => 'projects.reports'],
    ];

   
    $activeRoute = request()->route()?->getName();
@endphp

<aside id="sidebar"
    class="workspace-sidebar fixed inset-y-0 left-0 z-40 flex w-[230px] -translate-x-full flex-col lg:sticky lg:translate-x-0">
    <div class="flex items-center gap-3 border-b border-black/10 px-5 py-5">
        <div
            class="flex h-8 w-8 items-center justify-center rounded-[9px] bg-[#e8007d] text-sm font-semibold text-white shadow-[0_0_18px_rgba(232,0,125,0.35)]">
            <i class="ti ti-bolt text-[17px]"></i>
        </div>
        <div>
            <h1 class="font-['Syne'] text-[15px] font-extrabold tracking-wide text-[var(--text-strong)]">Daily<span class="text-[#e8007d]">Ops</span></h1>
            <p class="text-[10.5px] text-[var(--muted)]">Team workspace</p>
        </div>
    </div>

    <div class="px-3">
        <div class="workspace-switcher">
            <div
                class="flex h-8 w-8 items-center justify-center rounded-md border border-[#e8007d]/20 bg-[#e8007d]/10 text-xs font-semibold text-[#e8007d]">
                PH
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-[12.5px] font-medium text-[var(--text-strong)]">PH Marketing</p>
                <p class="truncate text-[10.5px] text-[var(--muted)]">Workspace</p>
            </div>
            <i class="ti ti-chevron-down text-[13px] text-[var(--muted)]"></i>
        </div>
    </div>

    <div class="custom-scroll flex-1 space-y-6 overflow-y-auto px-2 py-4">
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
                                <i class="ti ti-layout-dashboard"></i>
                            @elseif ($item['icon'] === 'table')
                                <i class="ti ti-table"></i>
                            @elseif ($item['icon'] === 'chart')
                                <i class="ti ti-chart-bar"></i>
                            @elseif ($item['icon'] === 'calendar')
                                <i class="ti ti-calendar"></i>
                            @else
                                <i class="ti ti-report"></i>
                            @endif
                        </span>
                        <span class="flex-1">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </section>
    </div>

    <div class="border-t border-[var(--line)] p-4">
        <div class="flex items-center gap-3">
            <div
                class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-[#e8007d] to-[#a0005a] font-['Syne'] text-xs font-semibold text-white shadow-[0_0_12px_rgba(232,0,125,0.3)]">
                AM
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-[var(--text-strong)]">Alex Martin</p>
                <p class="truncate text-xs text-[var(--muted)]">Pro plan</p>
            </div>
            <button class="icon-button h-8 w-8 p-0" type="button" aria-label="Settings">
                <i class="ti ti-settings text-base"></i>
            </button>
        </div>
    </div>
</aside>
