<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-['Syne'] text-base font-bold tracking-wide text-[#0a0a0a]">Details user</h2>
            <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center justify-center rounded-md bg-[#e8007d] px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-[#ff1a8c]">
                Modifier
            </a>
        </div>
    </x-slot>

    <div class="p-5">
        <div class="mx-auto max-w-4xl">
            <div class="rounded-[10px] border border-black/10 bg-white p-6 shadow-sm">
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nom</dt>
                        <dd class="mt-1 text-base font-semibold text-gray-900">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-base font-semibold text-gray-900">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Role</dt>
                        <dd class="mt-1 text-base font-semibold text-gray-900">{{ $user->role }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Verification email</dt>
                        <dd class="mt-1 text-base font-semibold text-gray-900">{{ $user->email_verified_at ? $user->email_verified_at->format('d/m/Y H:i') : 'Non verifie' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Cree le</dt>
                        <dd class="mt-1 text-base font-semibold text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Mis a jour le</dt>
                        <dd class="mt-1 text-base font-semibold text-gray-900">{{ $user->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
