<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="h-2 w-2 rounded-full bg-[#e8007d] shadow-[0_0_8px_rgba(232,0,125,0.5)]"></span>
                <div>
                    <h2 class="font-['Syne'] text-base font-bold tracking-wide text-[#0a0a0a]">Gestion des users</h2>
                    <p class="mt-1 text-[12.5px] text-[#888888]">Recherche, modification et suppression.</p>
                </div>
            </div>

            <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-[#e8007d] px-4 py-2 text-[13px] font-medium text-white shadow-[0_2px_14px_rgba(232,0,125,0.3)] transition hover:bg-[#ff1a8c]">
                <i class="ti ti-plus text-base"></i>
                Ajouter
            </a>
        </div>
    </x-slot>

    <div class="p-5">
        <div class="mx-auto max-w-7xl">
            @if (session('status'))
                <div class="mb-4 rounded-md border border-[#00a86b]/20 bg-[#00a86b]/10 p-4 text-[13px] font-medium text-[#00a86b]">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-md border border-red-600/20 bg-red-600/10 p-4 text-[13px] font-medium text-red-600">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="rounded-[10px] border border-black/10 bg-white shadow-sm">
                <div class="border-b border-black/10 p-4">
                    <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <x-text-input name="search" type="search" class="block w-full border-black/10 bg-[#f4f4f4] text-[13px] focus:border-[#e8007d] focus:ring-[#e8007d]" placeholder="Chercher par nom ou email" :value="$filters['search'] ?? ''" />
                        </div>

                        <div class="flex gap-2">
                            <x-primary-button>Filtrer</x-primary-button>
                            <a href="{{ route('users.index') }}" class="inline-flex items-center rounded-md border border-black/10 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-[#555555] hover:bg-[#f4f4f4]">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#f7f7f7]">
                            <tr>
                                <th class="px-6 py-3 text-left font-['Syne'] text-[10px] font-semibold uppercase tracking-[0.08em] text-[#999999]">User</th>
                                <th class="px-6 py-3 text-left font-['Syne'] text-[10px] font-semibold uppercase tracking-[0.08em] text-[#999999]">Role</th>
                                <th class="px-6 py-3 text-left font-['Syne'] text-[10px] font-semibold uppercase tracking-[0.08em] text-[#999999]">Verification</th>
                                <th class="px-6 py-3 text-left font-['Syne'] text-[10px] font-semibold uppercase tracking-[0.08em] text-[#999999]">Cree le</th>
                                <th class="px-6 py-3 text-right font-['Syne'] text-[10px] font-semibold uppercase tracking-[0.08em] text-[#999999]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5 bg-white">
                            @forelse ($users as $user)
                                <tr class="hover:bg-[#f7f7f7]">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-[#0a0a0a]">{{ $user->name }}</div>
                                        <div class="text-[12.5px] text-[#888888]">{{ $user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-[10.5px] font-medium {{ $user->role === 'admin' ? 'border-[#e8007d]/20 bg-[#e8007d]/10 text-[#e8007d]' : 'border-black/10 bg-[#eeeeee] text-[#555555]' }}">
                                            {{ $user->role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-[12.5px] text-[#888888]">
                                        {{ $user->email_verified_at ? 'Verifie' : 'Non verifie' }}
                                    </td>
                                    <td class="px-6 py-4 text-[12.5px] text-[#888888]">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-right text-[13px] font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('users.show', $user) }}" class="icon-button h-8 w-8 p-0"
                                                aria-label="Voir user" title="Voir user">
                                                <span class="material-symbols-rounded text-[18px]">visibility</span>
                                            </a>
                                            <a href="{{ route('users.edit', $user) }}" class="icon-button h-8 w-8 p-0"
                                                aria-label="Modifier user" title="Modifier user">
                                                <span class="material-symbols-rounded text-[18px]">edit</span>
                                            </a>
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="icon-button h-8 w-8 p-0"
                                                    aria-label="Supprimer user" title="Supprimer user">
                                                    <span class="material-symbols-rounded text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">Aucun utilisateur trouve.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
