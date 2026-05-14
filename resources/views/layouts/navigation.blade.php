<aside x-data="{ open: false }" class="hidden w-[230px] shrink-0 flex-col border-r border-black/10 bg-white shadow-[2px_0_12px_rgba(0,0,0,0.06)] sm:flex">
    <div class="relative flex items-center gap-3 border-b border-black/10 px-5 py-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="flex h-8 w-8 items-center justify-center rounded-[9px] bg-[#e8007d] text-white shadow-[0_0_18px_rgba(232,0,125,0.35)]">
                <i class="ti ti-bolt text-[17px]"></i>
            </span>
            <span class="font-['Syne'] text-[15px] font-extrabold tracking-wide text-[#0a0a0a]">
                Daily<span class="text-[#e8007d]">Ops</span>
            </span>
        </a>
    </div>

    <div class="mx-3 mt-4 flex items-center gap-3 rounded-[10px] border border-black/10 bg-[#f4f4f4] px-3 py-3">
        <span class="flex h-7 w-7 items-center justify-center rounded-md border border-[#e8007d]/20 bg-[#e8007d]/10 text-[#e8007d]">
            <i class="ti ti-building text-sm"></i>
        </span>
        <div class="min-w-0">
            <div class="truncate text-[12.5px] font-medium text-[#0a0a0a]">DailyOps</div>
            <div class="text-[10.5px] text-[#888888]">{{ Auth::user()->role === 'admin' ? 'Admin workspace' : 'Member workspace' }}</div>
        </div>
    </div>

    <nav class="min-h-0 flex-1 overflow-y-auto py-4">
        <div class="px-5 pb-2 font-['Syne'] text-[10px] uppercase tracking-[0.1em] text-[#bbbbbb]">Navigation</div>

        <a href="{{ route('dashboard') }}" class="relative mx-2 mb-1 flex items-center gap-3 rounded-md px-3 py-2 text-[13px] transition {{ request()->routeIs('dashboard') ? 'border border-[#e8007d]/20 bg-[#e8007d]/10 text-[#e8007d] before:absolute before:-left-2 before:top-1/2 before:h-4 before:w-[3px] before:-translate-y-1/2 before:rounded before:bg-[#e8007d]' : 'text-[#555555] hover:bg-[#f4f4f4] hover:text-[#0a0a0a]' }}">
            <i class="ti ti-layout-dashboard text-base"></i>
            <span class="flex-1">Dashboard</span>
        </a>

        @if (Auth::user()->role === 'admin')
            <a href="{{ route('users.index') }}" class="relative mx-2 mb-1 flex items-center gap-3 rounded-md px-3 py-2 text-[13px] transition {{ request()->routeIs('users.*') ? 'border border-[#e8007d]/20 bg-[#e8007d]/10 text-[#e8007d] before:absolute before:-left-2 before:top-1/2 before:h-4 before:w-[3px] before:-translate-y-1/2 before:rounded before:bg-[#e8007d]' : 'text-[#555555] hover:bg-[#f4f4f4] hover:text-[#0a0a0a]' }}">
                <i class="ti ti-users text-base"></i>
                <span class="flex-1">Users</span>
            </a>
        @endif

        <div class="px-5 pb-2 pt-5 font-['Syne'] text-[10px] uppercase tracking-[0.1em] text-[#bbbbbb]">Account</div>

        <a href="{{ route('profile.edit') }}" class="mx-2 mb-1 flex items-center gap-3 rounded-md px-3 py-2 text-[13px] text-[#555555] transition hover:bg-[#f4f4f4] hover:text-[#0a0a0a]">
            <i class="ti ti-settings text-base"></i>
            <span class="flex-1">Profile</span>
        </a>
    </nav>

    <div class="border-t border-black/10 p-4">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-[#e8007d] to-[#a0005a] font-['Syne'] text-xs font-semibold text-white shadow-[0_0_12px_rgba(232,0,125,0.3)]">
                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="truncate text-[12.5px] font-medium text-[#0a0a0a]">{{ Auth::user()->name }}</div>
                <div class="text-[11px] text-[#e8007d]">{{ Auth::user()->role }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-[#999999] transition hover:text-[#e8007d]" title="Log out">
                    <i class="ti ti-logout text-lg"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<div x-data="{ open: false }" class="sm:hidden">
    <button @click="open = ! open" class="fixed left-4 top-4 z-50 flex h-10 w-10 items-center justify-center rounded-md bg-white text-[#555555] shadow ring-1 ring-black/10">
        <i class="ti ti-menu-2 text-xl"></i>
    </button>

    <div x-show="open" @click="open = false" class="fixed inset-0 z-40 bg-black/30"></div>

    <aside x-show="open" class="fixed inset-y-0 left-0 z-50 flex w-[230px] flex-col border-r border-black/10 bg-white shadow-xl">
        <div class="flex items-center gap-3 border-b border-black/10 px-5 py-5">
            <span class="flex h-8 w-8 items-center justify-center rounded-[9px] bg-[#e8007d] text-white">
                <i class="ti ti-bolt"></i>
            </span>
            <span class="font-['Syne'] text-[15px] font-extrabold">Daily<span class="text-[#e8007d]">Ops</span></span>
        </div>
        <nav class="flex-1 py-4">
            <a href="{{ route('dashboard') }}" class="mx-2 mb-1 flex items-center gap-3 rounded-md px-3 py-2 text-[13px] text-[#555555]">
                <i class="ti ti-layout-dashboard"></i> Dashboard
            </a>
            @if (Auth::user()->role === 'admin')
                <a href="{{ route('users.index') }}" class="mx-2 mb-1 flex items-center gap-3 rounded-md px-3 py-2 text-[13px] text-[#555555]">
                    <i class="ti ti-users"></i> Users
                </a>
            @endif
        </nav>
    </aside>
</div>
