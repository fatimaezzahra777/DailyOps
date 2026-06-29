@extends('layouts.support')

@section('content')
    <section class="grid gap-6 lg:grid-cols-[0.78fr_1.22fr] lg:items-start">
        <div class="space-y-4">
            <div>
                <p class="text-sm font-semibold text-[var(--accent)]">DailyOps Support</p>
                <h1 class="mt-2 font-['Syne'] text-3xl font-bold text-[var(--text-strong)] sm:text-4xl">Contact your project manager</h1>
                <p class="mt-3 text-sm leading-6 text-[var(--text)]">
                    Enter the client email linked to your project. If the email exists in DailyOps, a temporary chat will open for 48 hours.
                </p>
            </div>

            <div class="rounded-md border border-[var(--line)] bg-white p-4 text-sm text-[var(--text)]">
                <div class="flex gap-3">
                    <i class="ti ti-clock-hour-4 mt-0.5 text-lg text-[var(--accent)]"></i>
                    <p>The conversation link automatically expires after 48 hours to keep support exchanges limited.</p>
                </div>
            </div>
        </div>

        <form action="{{ route('support.store') }}" method="POST" class="rounded-md border border-[var(--line)] bg-white p-5 shadow-sm sm:p-6" autocomplete="off">
            @csrf

            @if ($errors->any())
                <div class="mb-5 rounded-md border border-red-600/20 bg-red-600/10 p-4 text-sm text-red-700">
                    <p class="font-semibold">Please fix the highlighted fields.</p>
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="first_name" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">First name</label>
                    <input id="first_name" name="first_name" type="text" class="w-full px-4 py-3" value="{{ old('first_name') }}" required>
                    <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                </div>

                <div>
                    <label for="last_name" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Name</label>
                    <input id="last_name" name="last_name" type="text" class="w-full px-4 py-3" value="{{ old('last_name') }}" required>
                    <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Client email</label>
                    <input id="email" name="email" type="email" class="w-full px-4 py-3" value="{{ old('email') }}" placeholder="client@example.com" required>
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <div>
                    <label for="phone" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Phone</label>
                    <input id="phone" name="phone" type="tel" class="w-full px-4 py-3" value="{{ old('phone') }}">
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>

                <div class="sm:col-span-2">
                    <label for="title" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Chat title</label>
                    <input id="title" name="title" type="text" class="w-full px-4 py-3" value="{{ old('title') }}" required>
                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Problem description</label>
                    <textarea id="description" name="description" rows="6" class="w-full px-4 py-3" required>{{ old('description') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn-primary">
                    <i class="ti ti-message-circle mr-1"></i>
                    Open chat
                </button>
            </div>
        </form>
    </section>
@endsection
