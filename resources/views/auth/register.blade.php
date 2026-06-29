<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email', request('email'))" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="birth_date" :value="__('Date de naissance')" />
            <x-text-input id="birth_date" class="mt-1 block w-full" type="date" name="birth_date"
                :value="old('birth_date')" max="{{ today()->format('Y-m-d') }}" />
            <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-end">
            <a class="rounded-md text-sm text-[#555555] underline hover:text-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#c50064] focus:ring-offset-2" href="{{ route('login') }}">
                {{ __('Already registered??') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Create un compte') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
