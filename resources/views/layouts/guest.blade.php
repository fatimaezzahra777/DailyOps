<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'DailyOps') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-['DM_Sans'] text-[#0a0a0a] antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-[#f7f7f7] px-4 py-8">
            <div class="mb-6">
                <a href="/">
                    <img src="{{ asset('images/dailyops-logo.svg') }}" alt="DailyOps" class="h-auto w-[340px] max-w-full">
                </a>
            </div>

            <div class="w-full max-w-md overflow-hidden rounded-[10px] border border-black/10 bg-white px-6 py-6 shadow-sm">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
