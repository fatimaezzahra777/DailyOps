<x-app-layout>
    <x-slot name="header">
        <h2 class="font-['Syne'] text-base font-bold tracking-wide text-[#0a0a0a]">Ajouter un user</h2>
    </x-slot>

    <div class="p-5">
        <div class="mx-auto max-w-4xl">
            <div class="rounded-[10px] border border-black/10 bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('users.store') }}">
                    @include('users._form', ['submitLabel' => 'Creer'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
