<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="h-2 w-2 rounded-full bg-[#e8007d] shadow-[0_0_8px_rgba(232,0,125,0.5)]"></span>
                <div>
                    <h2 class="font-['Syne'] text-base font-bold tracking-wide text-[#0a0a0a]">
                        {{ ($isAdmin ?? false) ? 'Admin Dashboard' : 'Member Dashboard' }}
                    </h2>
                    <p class="mt-1 text-[12.5px] text-[#888888]">
                        {{ ($isAdmin ?? false) ? 'Statistiques, users et activite DailyOps.' : 'Vue projets et activite de travail.' }}
                    </p>
                </div>
            </div>

            @if ($isAdmin ?? false)
                <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-[#e8007d] px-4 py-2 text-[13px] font-medium text-white shadow-[0_2px_14px_rgba(232,0,125,0.3)] transition hover:bg-[#ff1a8c]">
                    <i class="ti ti-plus text-base"></i>
                    Add user
                </a>
            @endif
        </div>
    </x-slot>

    <div class="p-5">
        <div class="mx-auto max-w-7xl">
            @if (! ($isAdmin ?? false))
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="relative overflow-hidden rounded-[10px] border border-[#e8007d]/20 bg-white p-4 shadow-sm">
                        <div class="absolute right-0 top-0 h-20 w-20 bg-[radial-gradient(circle_at_top_right,rgba(232,0,125,0.16),transparent_70%)]"></div>
                        <p class="font-['Syne'] text-[10.5px] uppercase tracking-[0.08em] text-[#888888]">Mes projets</p>
                        <p class="mt-2 font-['Syne'] text-[26px] font-bold leading-none text-[#0a0a0a]">{{ $memberStats['total_projects'] }}</p>
                        <p class="mt-2 flex items-center gap-1 text-[11px] text-[#e8007d]"><i class="ti ti-layout-kanban text-xs"></i> Workspace projects</p>
                    </div>

                    <div class="rounded-[10px] border border-black/10 bg-white p-4 shadow-sm">
                        <p class="font-['Syne'] text-[10.5px] uppercase tracking-[0.08em] text-[#888888]">En cours</p>
                        <p class="mt-2 font-['Syne'] text-[26px] font-bold leading-none text-[#0a0a0a]">{{ $memberStats['in_progress'] }}</p>
                        <p class="mt-2 text-[11px] text-[#d97706]">Active tasks</p>
                    </div>

                    <div class="rounded-[10px] border border-black/10 bg-white p-4 shadow-sm">
                        <p class="font-['Syne'] text-[10.5px] uppercase tracking-[0.08em] text-[#888888]">Termines</p>
                        <p class="mt-2 font-['Syne'] text-[26px] font-bold leading-none text-[#0a0a0a]">{{ $memberStats['completed'] }}</p>
                        <p class="mt-2 text-[11px] text-[#00a86b]">Completed work</p>
                    </div>

                    <div class="rounded-[10px] border border-black/10 bg-white p-4 shadow-sm">
                        <p class="font-['Syne'] text-[10.5px] uppercase tracking-[0.08em] text-[#888888]">A faire</p>
                        <p class="mt-2 font-['Syne'] text-[26px] font-bold leading-none text-[#0a0a0a]">{{ $memberStats['pending'] }}</p>
                        <p class="mt-2 text-[11px] text-[#999999]">Pending projects</p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-3">
                    <div class="rounded-[10px] border border-black/10 bg-white shadow-sm lg:col-span-2">
                        <div class="flex items-center justify-between border-b border-black/10 px-5 py-4">
                            <div>
                                <h3 class="font-['Syne'] text-[14px] font-bold text-[#0a0a0a]">Projets recents</h3>
                                <p class="mt-1 text-[12.5px] text-[#888888]">Un espace de travail pour suivre tes projets, pas les comptes utilisateurs.</p>
                            </div>
                            <a href="{{ route('projects.index') }}" class="text-[13px] font-medium text-[#e8007d] hover:text-[#ff1a8c]">
                                Ouvrir board
                            </a>
                        </div>

                        <div class="divide-y divide-black/5">
                            @forelse ($memberProjects as $project)
                                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="font-medium text-[#0a0a0a]">{{ $project->name }}</div>
                                        <div class="mt-1 text-[12.5px] text-[#888888]">
                                            {{ $project->end_date ? 'Deadline: '.$project->end_date->format('d/m/Y') : 'Aucune deadline' }}
                                        </div>
                                    </div>
                                    <span class="inline-flex w-fit rounded-full border px-2.5 py-1 text-[10.5px] font-medium {{ $project->status === 'completed' ? 'border-[#00a86b]/20 bg-[#00a86b]/10 text-[#00a86b]' : ($project->status === 'in_progress' ? 'border-[#d97706]/20 bg-[#d97706]/10 text-[#d97706]' : 'border-[#e8007d]/20 bg-[#e8007d]/10 text-[#e8007d]') }}">
                                        {{ str($project->status)->replace('_', ' ')->title() }}
                                    </span>
                                </div>
                            @empty
                                <div class="px-5 py-10 text-center text-sm text-[#888888]">Aucun projet pour le moment.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-[10px] border border-black/10 bg-white p-5 shadow-sm">
                        <h3 class="font-['Syne'] text-[14px] font-bold text-[#0a0a0a]">Espace membre</h3>
                        <div class="mt-5 space-y-3">
                            <a href="{{ route('projects.index') }}" class="flex items-center justify-between rounded-md border border-black/10 px-4 py-3 text-[13px] font-medium text-[#555555] transition hover:border-[#e8007d]/20 hover:bg-[#e8007d]/10 hover:text-[#e8007d]">
                                Voir les projets
                                <i class="ti ti-arrow-right"></i>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center justify-between rounded-md border border-black/10 px-4 py-3 text-[13px] font-medium text-[#555555] transition hover:border-[#e8007d]/20 hover:bg-[#e8007d]/10 hover:text-[#e8007d]">
                                Mon profil
                                <i class="ti ti-settings"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    <div class="relative overflow-hidden rounded-[10px] border border-[#e8007d]/20 bg-white p-4 shadow-sm">
                        <div class="absolute right-0 top-0 h-20 w-20 bg-[radial-gradient(circle_at_top_right,rgba(232,0,125,0.16),transparent_70%)]"></div>
                        <p class="font-['Syne'] text-[10.5px] uppercase tracking-[0.08em] text-[#888888]">Total users</p>
                        <p class="mt-2 font-['Syne'] text-[26px] font-bold leading-none text-[#0a0a0a]">{{ $stats['total_users'] }}</p>
                        <p class="mt-2 flex items-center gap-1 text-[11px] text-[#e8007d]"><i class="ti ti-users text-xs"></i> All accounts</p>
                    </div>

                    <div class="rounded-[10px] border border-black/10 bg-white p-4 shadow-sm">
                        <p class="font-['Syne'] text-[10.5px] uppercase tracking-[0.08em] text-[#888888]">Members</p>
                        <p class="mt-2 font-['Syne'] text-[26px] font-bold leading-none text-[#0a0a0a]">{{ $stats['members'] }}</p>
                        <p class="mt-2 text-[11px] text-[#00a86b]">Default role</p>
                    </div>

                    <div class="rounded-[10px] border border-black/10 bg-white p-4 shadow-sm">
                        <p class="font-['Syne'] text-[10.5px] uppercase tracking-[0.08em] text-[#888888]">Verifies</p>
                        <p class="mt-2 font-['Syne'] text-[26px] font-bold leading-none text-[#0a0a0a]">{{ $stats['verified'] }}</p>
                        <p class="mt-2 text-[11px] text-[#999999]">Email verified</p>
                    </div>

                    <div class="rounded-[10px] border border-black/10 bg-white p-4 shadow-sm">
                        <p class="font-['Syne'] text-[10.5px] uppercase tracking-[0.08em] text-[#888888]">Aujourd'hui</p>
                        <p class="mt-2 font-['Syne'] text-[26px] font-bold leading-none text-[#0a0a0a]">{{ $stats['created_today'] }}</p>
                        <p class="mt-2 text-[11px] text-[#999999]">New accounts</p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-3">
                    <div class="rounded-[10px] border border-black/10 bg-white shadow-sm lg:col-span-2">
                        <div class="flex items-center justify-between border-b border-black/10 px-5 py-4">
                            <div>
                                <h3 class="font-['Syne'] text-[14px] font-bold text-[#0a0a0a]">Utilisateurs recents</h3>
                                <p class="mt-1 text-[12.5px] text-[#888888]">Les derniers comptes ajoutes.</p>
                            </div>
                            <a href="{{ route('users.index') }}" class="text-[13px] font-medium text-[#e8007d] hover:text-[#ff1a8c]">
                                Tout voir
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-[#f7f7f7]">
                                    <tr>
                                        <th class="px-5 py-3 text-left font-['Syne'] text-[10px] font-semibold uppercase tracking-[0.08em] text-[#999999]">Nom</th>
                                        <th class="px-5 py-3 text-left font-['Syne'] text-[10px] font-semibold uppercase tracking-[0.08em] text-[#999999]">Role</th>
                                        <th class="px-5 py-3 text-left font-['Syne'] text-[10px] font-semibold uppercase tracking-[0.08em] text-[#999999]">Cree le</th>
                                        <th class="px-6 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/5 bg-white">
                                    @forelse ($recentUsers as $user)
                                        <tr class="hover:bg-[#f7f7f7]">
                                            <td class="px-5 py-4">
                                                <div class="font-medium text-[#0a0a0a]">{{ $user->name }}</div>
                                                <div class="text-[12.5px] text-[#888888]">{{ $user->email }}</div>
                                            </td>
                                            <td class="px-5 py-4">
                                                <span class="inline-flex rounded-full border px-2.5 py-1 text-[10.5px] font-medium {{ $user->role === 'admin' ? 'border-[#e8007d]/20 bg-[#e8007d]/10 text-[#e8007d]' : 'border-black/10 bg-[#eeeeee] text-[#555555]' }}">
                                                    {{ $user->role }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 text-[12.5px] text-[#888888]">{{ $user->created_at->format('d/m/Y') }}</td>
                                            <td class="px-5 py-4 text-right text-[13px] font-medium">
                                                <a href="{{ route('users.edit', $user) }}" class="text-[#e8007d] hover:text-[#ff1a8c]">Modifier</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Aucun utilisateur pour le moment.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="rounded-[10px] border border-black/10 bg-white p-5 shadow-sm">
                        <h3 class="font-['Syne'] text-[14px] font-bold text-[#0a0a0a]">Actions rapides</h3>
                        <div class="mt-5 space-y-3">
                            <a href="{{ route('users.index') }}" class="flex items-center justify-between rounded-md border border-black/10 px-4 py-3 text-[13px] font-medium text-[#555555] transition hover:border-[#e8007d]/20 hover:bg-[#e8007d]/10 hover:text-[#e8007d]">
                                Gerer les users
                                <i class="ti ti-arrow-right"></i>
                            </a>
                            <a href="{{ route('users.create') }}" class="flex items-center justify-between rounded-md border border-black/10 px-4 py-3 text-[13px] font-medium text-[#555555] transition hover:border-[#e8007d]/20 hover:bg-[#e8007d]/10 hover:text-[#e8007d]">
                                Ajouter un user
                                <i class="ti ti-plus"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
