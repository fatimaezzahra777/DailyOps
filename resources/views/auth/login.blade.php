<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Daily_Ops</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Tabler Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>

<body class="min-h-screen bg-white flex items-center justify-center relative overflow-hidden">

    <!-- Background Glow -->
    <div class="absolute -top-40 -left-40 w-[500px] h-[500px] bg-pink-500/10 rounded-full blur-3xl"></div>

    <div class="absolute -bottom-40 -right-40 w-[400px] h-[400px] bg-pink-400/10 rounded-full blur-3xl"></div>

    <div class="w-full max-w-md relative z-10">

        <!-- Logo -->
        <div class="flex items-center justify-center gap-3 mb-6">

            

            <h1 class="text-3xl font-black text-black"
                style="font-family: 'Syne', sans-serif;">
                Daily<span class="text-pink-600">Ops</span>
            </h1>

        </div>

        <!-- Chips -->
        <div class="flex justify-center gap-2 mb-8 flex-wrap">

            <div class="px-3 py-1 rounded-full bg-pink-100 text-pink-600 text-xs flex items-center gap-1">
                <i class="ti ti-shield-check"></i>
                Secure
            </div>

            <div class="px-3 py-1 rounded-full bg-pink-100 text-pink-600 text-xs flex items-center gap-1">
                <i class="ti ti-bolt"></i>
                Fast
            </div>

            <div class="px-3 py-1 rounded-full bg-pink-100 text-pink-600 text-xs flex items-center gap-1">
                <i class="ti ti-users"></i>
                Collaborative
            </div>

        </div>

        <!-- Login Card -->
        <div class="bg-white border border-gray-200 rounded-3xl p-8 shadow-2xl shadow-black/5">

            <h2 class="text-3xl font-bold text-black mb-2"
                style="font-family: 'Syne', sans-serif;">
                Welcome back
            </h2>

            <p class="text-gray-500 mb-8 text-sm">
                Sign in to access your workspace
            </p>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">

                @csrf

                <!-- Email -->
                <div class="mb-5">

                    <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2 font-semibold">
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="admin@dailyops.com"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/10 outline-none transition"
                    >

                    @error('email')
                        <p class="text-red-500 text-sm mt-2">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <!-- Password -->
                <div class="mb-5">

                    <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2 font-semibold">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        required
                        placeholder="Enter your password"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/10 outline-none transition"
                    >

                    @error('password')
                        <p class="text-red-500 text-sm mt-2">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <!-- Remember -->
                <div class="flex items-center justify-between mb-8">

                    <label class="flex items-center gap-2 text-sm text-gray-600">

                        <input
                            type="checkbox"
                            name="remember"
                            class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                        >

                        Remember me

                    </label>

                    @if (Route::has('password.request'))

                        <a href="{{ route('password.request') }}"
                           class="text-sm text-pink-600 hover:text-pink-700 transition">
                            Forgot password?
                        </a>

                    @endif

                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    class="w-full py-3 rounded-xl bg-pink-600 hover:bg-pink-700 transition text-white font-bold shadow-lg shadow-pink-500/30"
                    style="font-family: 'Syne', sans-serif;"
                >
                    Sign In
                </button>

            </form>

        </div>

        <!-- Footer -->
        <p class="text-center text-sm text-gray-400 mt-6">
            Daily_Ops Project Management Platform
        </p>

    </div>

</body>
</html>