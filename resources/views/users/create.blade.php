<x-app-layout>
    <x-slot name="header">
        <h2 class="font-['Syne'] text-base font-bold tracking-wide text-[#0a0a0a]">Ajouter un user</h2>
    </x-slot>

    <div class="bg-[#f7f7f7] px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl rounded-[14px] border border-black/10 bg-white px-6 py-8 shadow-[0_8px_30px_rgba(0,0,0,0.08)] sm:px-10 lg:px-14 lg:py-12">
            <div class="mb-10">
                <h1 class="font-['Syne'] text-[46px] font-extrabold leading-[0.98] tracking-tight text-[#050700] sm:text-[64px] lg:text-[82px]">
                    Ajouter<br>un user
                </h1>
                <p class="mt-5 text-[15px] text-[#6b7280] sm:text-base">
                    Creez un compte membre ou administrateur selon ses responsabilites.
                </p>
            </div>

            <div>
                <form method="POST" action="{{ route('users.store') }}">
                    @include('users._form', ['submitLabel' => 'Creer'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
