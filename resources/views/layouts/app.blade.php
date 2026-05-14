<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts & icons -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-['DM_Sans'] text-[#0a0a0a]">
        <div class="flex h-screen overflow-hidden bg-[#f7f7f7]">
            @include('layouts.navigation')

            <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
                @isset($header)
                    <header class="border-b border-black/10 bg-white shadow-[0_1px_8px_rgba(0,0,0,0.06)]">
                        <div class="px-5 py-4">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="min-h-0 flex-1 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
