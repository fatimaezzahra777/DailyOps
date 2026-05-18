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
        <div class="flex items-center justify-center gap-3 mb-6">

            

            <span class="flex h-8 w-8 items-center justify-center rounded-[9px] bg-[#e8007d] text-white shadow-[0_0_18px_rgba(232,0,125,0.35)]">
                <i class="ti ti-bolt text-[17px]"></i>
            </span>

            <h1 class="text-[20px] font-black text-black"
                style="font-family: 'Syne', sans-serif;">
                Daily<span class="text-[#e8007d]">Ops</span>
            </h1>

        </div>

        <div class="flex justify-center gap-2 mb-8 flex-wrap">
            <div class="px-3 py-1 rounded-full border border-[#e8007d]/20 bg-[#e8007d]/10 text-[#e8007d] text-xs flex items-center gap-1">
                <i class="ti ti-shield-check"></i>
                Secure
            </div>

            <div class="px-3 py-1 rounded-full border border-[#e8007d]/20 bg-[#e8007d]/10 text-[#e8007d] text-xs flex items-center gap-1">
                <i class="ti ti-bolt"></i>
                Fast
            </div>

            <div class="px-3 py-1 rounded-full border border-[#e8007d]/20 bg-[#e8007d]/10 text-[#e8007d] text-xs flex items-center gap-1">
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
                        class="w-full px-4 py-3 rounded-md border border-black/10 bg-[#f4f4f4] focus:border-[#e8007d] focus:ring-4 focus:ring-[#e8007d]/10 outline-none transition"
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
                        class="w-full px-4 py-3 rounded-md border border-black/10 bg-[#f4f4f4] focus:border-[#e8007d] focus:ring-4 focus:ring-[#e8007d]/10 outline-none transition"
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
                            class="rounded border-gray-300 text-[#e8007d] focus:ring-[#e8007d]"
                        >

                        Remember me

                    </label>

                    @if (Route::has('password.request'))

                        <a href="{{ route('password.request') }}"
                            class="text-sm text-[#e8007d] hover:text-[#ff1a8c] transition">
                            Forgot password?
                        </a>

                    @endif

                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    class="w-full py-3 rounded-md bg-[#e8007d] hover:bg-[#ff1a8c] transition text-white font-bold shadow-[0_2px_14px_rgba(232,0,125,0.3)]"
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
