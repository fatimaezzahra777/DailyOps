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

                <a href="{{ route('users.edit', $user) }}" class="icon-button h-11 w-11 p-0"
                    aria-label="Modifier user" title="Modifier user">
                    <span class="material-symbols-rounded text-[22px]" aria-hidden="true">edit</span>
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

            <section class="mt-10">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="h-7 w-[3px] rounded-full bg-[#c90068]"></span>
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-[#c90068]">
                            {{ $user->isAdmin() ? 'Tous les projets' : 'Projets de cette personne' }}
                        </p>
                    </div>
                    <span class="inline-flex rounded-full border border-black/10 bg-[#f8fafc] px-3 py-1 text-[11px] font-bold text-[#6b7280]">
                        {{ $visibleProjects->count() }} projets
                    </span>
                </div>

                @php
                    $projectCard = function ($project) {
                        $statusClass = match ($project->status) {
                            'completed' => 'border-[#00a86b]/20 bg-[#00a86b]/10 text-[#00a86b]',
                            'in_progress' => 'border-[#f59e0b]/20 bg-[#f59e0b]/10 text-[#b45309]',
                            default => 'border-[#c90068]/20 bg-[#c90068]/10 text-[#c90068]',
                        };

                        return [
                            'statusClass' => $statusClass,
                            'statusLabel' => str($project->status)->replace('_', ' ')->title(),
                            'deadline' => $project->end_date?->format('d M Y') ?? 'No deadline',
                        ];
                    };
                @endphp

                @if ($user->isAdmin())
                    <div class="grid gap-4 md:grid-cols-2">
                        @forelse ($visibleProjects as $project)
                            @php($meta = $projectCard($project))
                            <article class="rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-sm font-bold text-[#111827]">{{ $project->name }}</h3>
                                        <p class="mt-1 text-xs text-[#6b7280]">Manager: {{ $project->manager?->name ?? 'Not assigned' }}</p>
                                    </div>
                                    <a href="{{ route('projects.show', $project) }}" class="icon-button h-8 w-8 p-0"
                                        aria-label="Voir projet" title="Voir projet">
                                        <span class="material-symbols-rounded text-[18px]">visibility</span>
                                    </a>
                                </div>
                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-[10.5px] font-bold {{ $meta['statusClass'] }}">
                                        {{ $meta['statusLabel'] }}
                                    </span>
                                    <span class="inline-flex rounded-full border border-black/10 bg-white px-2.5 py-1 text-[10.5px] font-bold text-[#6b7280]">
                                        {{ $meta['deadline'] }}
                                    </span>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-[10px] border border-dashed border-[#dce4ef] bg-[#f8fafc] p-5 text-sm text-[#6b7280]">Aucun projet trouve.</p>
                        @endforelse
                    </div>
                @else
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div>
                            <h3 class="mb-3 text-sm font-extrabold text-[#111827]">Projets crees / geres</h3>
                            <div class="space-y-3">
                                @forelse ($managedProjects as $project)
                                    @php($meta = $projectCard($project))
                                    <article class="rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <h4 class="text-sm font-bold text-[#111827]">{{ $project->name }}</h4>
                                                <p class="mt-1 text-xs text-[#6b7280]">{{ $meta['deadline'] }}</p>
                                            </div>
                                            <a href="{{ route('projects.show', $project) }}" class="icon-button h-8 w-8 p-0"
                                                aria-label="Voir projet" title="Voir projet">
                                                <span class="material-symbols-rounded text-[18px]">visibility</span>
                                            </a>
                                        </div>
                                        <span class="mt-3 inline-flex rounded-full border px-2.5 py-1 text-[10.5px] font-bold {{ $meta['statusClass'] }}">
                                            {{ $meta['statusLabel'] }}
                                        </span>
                                    </article>
                                @empty
                                    <p class="rounded-[10px] border border-dashed border-[#dce4ef] bg-[#f8fafc] p-5 text-sm text-[#6b7280]">Aucun projet gere.</p>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <h3 class="mb-3 text-sm font-extrabold text-[#111827]">Projets assignes / collaboration</h3>
                            <div class="space-y-3">
                                @forelse ($assignedProjects as $project)
                                    @php($meta = $projectCard($project))
                                    <article class="rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <h4 class="text-sm font-bold text-[#111827]">{{ $project->name }}</h4>
                                                <p class="mt-1 text-xs text-[#6b7280]">Manager: {{ $project->manager?->name ?? 'Not assigned' }}</p>
                                            </div>
                                            <a href="{{ route('projects.show', $project) }}" class="icon-button h-8 w-8 p-0"
                                                aria-label="Voir projet" title="Voir projet">
                                                <span class="material-symbols-rounded text-[18px]">visibility</span>
                                            </a>
                                        </div>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <span class="inline-flex rounded-full border px-2.5 py-1 text-[10.5px] font-bold {{ $meta['statusClass'] }}">
                                                {{ $meta['statusLabel'] }}
                                            </span>
                                            <span class="inline-flex rounded-full border border-black/10 bg-white px-2.5 py-1 text-[10.5px] font-bold text-[#6b7280]">
                                                {{ $meta['deadline'] }}
                                            </span>
                                        </div>
                                    </article>
                                @empty
                                    <p class="rounded-[10px] border border-dashed border-[#dce4ef] bg-[#f8fafc] p-5 text-sm text-[#6b7280]">Aucun projet assigne.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif
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
