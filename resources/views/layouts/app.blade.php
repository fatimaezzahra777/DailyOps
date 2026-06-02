<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="color-scheme" content="light">

        <title>{{ config('app.name', 'DailyOps') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-['DM_Sans'] text-[#0a0a0a]">
        @isset($slot)
            <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-black/50 backdrop-blur-sm lg:hidden"></div>

            <div class="flex h-screen overflow-hidden bg-[#f7f7f7]">
                @include('partials.sidebar')

                <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
                    @isset($header)
                        <header class="border-b border-black/10 bg-white shadow-[0_1px_8px_rgba(0,0,0,0.06)]">
                            <div class="px-4 py-4 pl-16 sm:px-5 sm:pl-5">
                                <button id="menu-btn" class="icon-button fixed left-4 top-3 z-20 lg:hidden" type="button" aria-label="Open menu">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
                                    </svg>
                                </button>

                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <main class="min-h-0 flex-1 overflow-y-auto">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        @else
            <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-black/50 backdrop-blur-sm lg:hidden"></div>

            <div class="app-shell">
                @include('partials.sidebar')

                <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
                    @include('partials.navbar')

                    <main class="custom-scroll min-h-0 flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                        @if (session('success'))
                            <div class="mb-6 rounded-md border border-[#00a86b]/20 bg-[#00a86b]/10 px-4 py-3 text-sm font-medium text-[#00a86b]">
                                {{ session('success') }}
                            </div>
                        @endif

                        @yield('content')
                    </main>
                </div>
            </div>
        @endisset
    </body>
</html>
