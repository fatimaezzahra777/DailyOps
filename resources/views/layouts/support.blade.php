<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="color-scheme" content="light">

        <title>{{ config('app.name', 'DailyOps') }} - Support</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-['DM_Sans'] text-[var(--text-strong)] antialiased">
        <main class="min-h-screen bg-[var(--page-alt)] px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
                <header class="flex items-center justify-between gap-4 border-b border-[var(--line)] pb-5">
                    <a href="{{ route('support.create') }}" class="block">
                        <img src="{{ asset('images/dailyops-logo.svg') }}" alt="DailyOps" class="h-auto w-44">
                    </a>
                    <span class="rounded-md border border-[var(--accent-line)] bg-[var(--accent-soft)] px-3 py-2 text-xs font-semibold text-[var(--accent)]">
                        Client support
                    </span>
                </header>

                @yield('content')
            </div>
        </main>
    </body>
</html>
