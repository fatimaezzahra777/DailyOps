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

<body class="min-h-screen bg-[#f7f7f7] flex items-center justify-center relative overflow-hidden px-4 py-8">

    <div class="w-full max-w-md relative z-10">

        <!-- Logo -->
        <div class="flex items-center justify-center mb-6">
            <img src="{{ asset('images/dailyops-logo.svg') }}" alt="DailyOps" class="h-auto w-[340px] max-w-full">
        </div>

        <div class="flex justify-center gap-2 mb-8 flex-wrap">
            <div class="px-3 py-1 rounded-full border border-[#c50064]/20 bg-[#c50064]/10 text-[#c50064] text-xs flex items-center gap-1">
                <i class="ti ti-shield-check"></i>
                Secure
            </div>

            <div class="px-3 py-1 rounded-full border border-[#c50064]/20 bg-[#c50064]/10 text-[#c50064] text-xs flex items-center gap-1">
                <i class="ti ti-bolt"></i>
                Fast
            </div>

            <div class="px-3 py-1 rounded-full border border-[#c50064]/20 bg-[#c50064]/10 text-[#c50064] text-xs flex items-center gap-1">
                <i class="ti ti-users"></i>
                Collaborative
            </div>

        </div>

        <!-- Login Card -->
        <div class="rounded-[10px] border border-black/10 bg-white p-8 shadow-sm">

            <h2 class="text-xl font-bold text-black mb-2"
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
                        class="w-full px-4 py-3 rounded-md border border-black/10 bg-[#f4f4f4] focus:border-[#c50064] focus:ring-4 focus:ring-[#c50064]/10 outline-none transition"
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
                        class="w-full px-4 py-3 rounded-md border border-black/10 bg-[#f4f4f4] focus:border-[#c50064] focus:ring-4 focus:ring-[#c50064]/10 outline-none transition"
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
                            class="rounded border-gray-300 text-[#c50064] focus:ring-[#c50064]"
                        >

                        Remember me

                    </label>

                    @if (Route::has('password.request'))

                        <a href="{{ route('password.request') }}"
                            class="text-sm text-[#c50064] hover:text-[#a90056] transition">
                            Forgot password?
                        </a>

                    @endif

                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    class="w-full py-3 rounded-md bg-[#c50064] hover:bg-[#a90056] transition text-white font-bold shadow-[0_2px_14px_rgba(197,0,100,0.3)]"
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
