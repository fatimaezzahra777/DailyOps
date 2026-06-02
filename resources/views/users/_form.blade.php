@csrf

<div class="space-y-10">
    <section>
        <div class="mb-5 flex items-center gap-3">
            <span class="h-7 w-[3px] rounded-full bg-[#c90068]"></span>
            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-[#c90068]">Vos coordonnees</p>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="name" class="mb-2 block text-[13px] font-semibold text-[#374151]">Nom complet</label>
                <input id="name" name="name" type="text"
                    class="block w-full rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] px-4 py-4 text-[15px] text-[#0a0a0a] placeholder:text-[#94a3b8] transition focus:border-[#c90068] focus:bg-white focus:ring-4 focus:ring-[#c90068]/10"
                    value="{{ old('name', $user->name ?? '') }}" placeholder="Votre nom *" required autofocus>
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <label for="email" class="mb-2 block text-[13px] font-semibold text-[#374151]">Email</label>
                <input id="email" name="email" type="email"
                    class="block w-full rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] px-4 py-4 text-[15px] text-[#0a0a0a] placeholder:text-[#94a3b8] transition focus:border-[#c90068] focus:bg-white focus:ring-4 focus:ring-[#c90068]/10"
                    value="{{ old('email', $user->email ?? '') }}" placeholder="Votre email *" required>
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>
        </div>
    </section>

    <section>
        <div class="mb-5 flex items-center gap-3">
            <span class="h-7 w-[3px] rounded-full bg-[#c90068]"></span>
            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-[#c90068]">Acces au compte</p>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="password" class="mb-2 block text-[13px] font-semibold text-[#374151]">
                    {{ isset($user) ? 'Nouveau mot de passe' : 'Mot de passe' }}
                </label>
                <input id="password" name="password" type="password"
                    class="block w-full rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] px-4 py-4 text-[15px] text-[#0a0a0a] placeholder:text-[#94a3b8] transition focus:border-[#c90068] focus:bg-white focus:ring-4 focus:ring-[#c90068]/10"
                    placeholder="{{ isset($user) ? 'Laisser vide pour ne pas changer' : 'Mot de passe *' }}"
                    @required(! isset($user)) autocomplete="new-password">
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-[13px] font-semibold text-[#374151]">Confirmer le mot de passe</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                    class="block w-full rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] px-4 py-4 text-[15px] text-[#0a0a0a] placeholder:text-[#94a3b8] transition focus:border-[#c90068] focus:bg-white focus:ring-4 focus:ring-[#c90068]/10"
                    placeholder="Confirmer le mot de passe *" @required(! isset($user)) autocomplete="new-password">
            </div>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 pt-1 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('users.index') }}" class="inline-flex items-center justify-center rounded-[10px] border border-[#dce4ef] bg-white px-5 py-4 text-[12px] font-extrabold uppercase tracking-[0.14em] text-[#374151] transition hover:bg-[#f8fafc]">
            Annuler
        </a>
        <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-[10px] bg-[#c90068] px-6 py-4 text-[13px] font-extrabold uppercase tracking-[0.08em] text-white shadow-[0_10px_24px_rgba(201,0,104,0.22)] transition hover:bg-[#e8007d] focus:outline-none focus:ring-4 focus:ring-[#c90068]/20 sm:max-w-[420px]">
            {{ $submitLabel }}
        </button>
    </div>
</div>
