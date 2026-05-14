@csrf

<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Nom" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email ?? '')" required />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

    <div>
        <x-input-label for="password" :value="isset($user) ? 'Nouveau mot de passe' : 'Mot de passe'" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="! isset($user)" autocomplete="new-password" />
        <x-input-error class="mt-2" :messages="$errors->get('password')" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="! isset($user)" autocomplete="new-password" />
    </div>
</div>

<div class="mt-8 flex items-center justify-end gap-3">
    <a href="{{ route('users.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
        Annuler
    </a>
    <x-primary-button>{{ $submitLabel }}</x-primary-button>
</div>
