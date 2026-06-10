<x-app-layout>
    <x-slot name="header">
        <h2 class="font-['Syne'] text-base font-bold tracking-wide text-[#0a0a0a]">Modifier {{ $user->name }}</h2>
    </x-slot>

    <div class="bg-[#f7f7f7] px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl rounded-[14px] border border-black/10 bg-white px-6 py-8 shadow-[0_8px_30px_rgba(0,0,0,0.08)] sm:px-10 lg:px-14 lg:py-12">
            <div class="mb-10">
                <h1 class="font-['Syne'] text-[44px] font-extrabold leading-[0.98] tracking-tight text-[#050700] sm:text-[60px] lg:text-[76px]">
                    Modifier<br>un user
                </h1>
                <p class="mt-5 text-[15px] text-[#6b7280] sm:text-base">
                    Mettez a jour les informations de connexion et le role de {{ $user->name }}.
                </p>
            </div>

            <div>
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @method('PUT')
                    @include('users._form', ['submitLabel' => 'Enregistrer'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
