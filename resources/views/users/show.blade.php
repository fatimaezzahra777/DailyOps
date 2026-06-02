<x-app-layout>
    <x-slot name="header">
        <h2 class="font-['Syne'] text-base font-bold tracking-wide text-[#0a0a0a]">Details user</h2>
    </x-slot>

    <div class="bg-[#f7f7f7] px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl rounded-[14px] border border-black/10 bg-white px-6 py-8 shadow-[0_8px_30px_rgba(0,0,0,0.08)] sm:px-10 lg:px-14 lg:py-12">
            <div class="mb-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="font-['Syne'] text-[44px] font-extrabold leading-[0.98] tracking-tight text-[#050700] sm:text-[60px] lg:text-[76px]">
                        Afficher<br>un user
                    </h1>
                    <p class="mt-5 text-[15px] text-[#6b7280] sm:text-base">
                        Consultez les informations et l'etat du compte membre.
                    </p>
                </div>

                <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center justify-center gap-2 rounded-[10px] bg-[#c90068] px-6 py-4 text-[12px] font-extrabold uppercase tracking-[0.1em] text-white shadow-[0_10px_24px_rgba(201,0,104,0.22)] transition hover:bg-[#e8007d] focus:outline-none focus:ring-4 focus:ring-[#c90068]/20">
                    <span class="material-symbols-rounded text-[19px]" aria-hidden="true">edit</span>
                    Modifier le user
                </a>
            </div>

            <section>
                <div class="mb-5 flex items-center gap-3">
                    <span class="h-7 w-[3px] rounded-full bg-[#c90068]"></span>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-[#c90068]">Informations personnelles</p>
                </div>

                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] px-4 py-4">
                        <dt class="text-[12px] font-semibold text-[#6b7280]">Nom complet</dt>
                        <dd class="mt-2 text-[15px] font-bold text-[#111827]">{{ $user->name }}</dd>
                    </div>
                    <div class="rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] px-4 py-4">
                        <dt class="text-[12px] font-semibold text-[#6b7280]">Email</dt>
                        <dd class="mt-2 break-all text-[15px] font-bold text-[#111827]">{{ $user->email }}</dd>
                    </div>
                </dl>
            </section>

            <section class="mt-10">
                <div class="mb-5 flex items-center gap-3">
                    <span class="h-7 w-[3px] rounded-full bg-[#c90068]"></span>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-[#c90068]">Acces au compte</p>
                </div>

                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] px-4 py-4">
                        <dt class="text-[12px] font-semibold text-[#6b7280]">Role</dt>
                        <dd class="mt-2">
                            <span class="inline-flex rounded-full border border-[#c90068]/20 bg-[#c90068]/10 px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.1em] text-[#c90068]">
                                {{ $user->role }}
                            </span>
                        </dd>
                    </div>
                    <div class="rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] px-4 py-4">
                        <dt class="text-[12px] font-semibold text-[#6b7280]">Verification email</dt>
                        <dd class="mt-2 text-[15px] font-bold text-[#111827]">
                            {{ $user->email_verified_at ? $user->email_verified_at->format('d/m/Y H:i') : 'Non verifie' }}
                        </dd>
                    </div>
                    <div class="rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] px-4 py-4">
                        <dt class="text-[12px] font-semibold text-[#6b7280]">Cree le</dt>
                        <dd class="mt-2 text-[15px] font-bold text-[#111827]">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] px-4 py-4">
                        <dt class="text-[12px] font-semibold text-[#6b7280]">Mis a jour le</dt>
                        <dd class="mt-2 text-[15px] font-bold text-[#111827]">{{ $user->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </section>

            <div class="mt-10">
                <a href="{{ route('users.index') }}" class="inline-flex items-center justify-center gap-2 rounded-[10px] border border-[#dce4ef] bg-white px-5 py-4 text-[12px] font-extrabold uppercase tracking-[0.14em] text-[#374151] transition hover:bg-[#f8fafc]">
                    <span class="material-symbols-rounded text-[18px]" aria-hidden="true">arrow_back</span>
                    Retour
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
