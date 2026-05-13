<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <title>DailyOps</title>
</head>

<body>
    <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-black/50 backdrop-blur-sm lg:hidden"></div>

    <div class="app-shell">
        @include('partials.sidebar')

        <div class="flex min-h-screen min-w-0 flex-1 flex-col">
            @include('partials.navbar')

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>
