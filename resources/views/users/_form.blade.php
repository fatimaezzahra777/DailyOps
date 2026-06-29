@csrf

<div class="space-y-10">
    <section>
        <div class="mb-5 flex items-center gap-3">
            <span class="h-7 w-[3px] rounded-full bg-[#c50064]"></span>
            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-[#c50064]">Your details</p>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="name" class="mb-2 block text-[13px] font-semibold text-[#374151]">Full name</label>
                <input id="name" name="name" type="text"
                    class="block w-full rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] px-4 py-4 text-[15px] text-[#0a0a0a] placeholder:text-[#94a3b8] transition focus:border-[#c50064] focus:bg-white focus:ring-4 focus:ring-[#c50064]/10"
                    value="{{ old('name', $user->name ?? '') }}" placeholder="Your name *" required autofocus>
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <label for="email" class="mb-2 block text-[13px] font-semibold text-[#374151]">E-mail</label>
                <input id="email" name="email" type="email"
                    class="block w-full rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] px-4 py-4 text-[15px] text-[#0a0a0a] placeholder:text-[#94a3b8] transition focus:border-[#c50064] focus:bg-white focus:ring-4 focus:ring-[#c50064]/10"
                    value="{{ old('email', $user->email ?? '') }}" placeholder="Your email *" required>
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <label for="birth_date" class="mb-2 block text-[13px] font-semibold text-[#374151]">Date de naissance</label>
                <input id="birth_date" name="birth_date" type="date"
                    class="block w-full rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] px-4 py-4 text-[15px] text-[#0a0a0a] transition focus:border-[#c50064] focus:bg-white focus:ring-4 focus:ring-[#c50064]/10"
                    value="{{ old('birth_date', isset($user) ? $user->birth_date?->format('Y-m-d') : '') }}"
                    max="{{ today()->format('Y-m-d') }}">
                <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
            </div>
        </div>
    </section>

    @if (! isset($user) || ! auth()->user()->is($user))
        <section>
            <div class="mb-5 flex items-center gap-3">
                <span class="h-7 w-[3px] rounded-full bg-[#c90068]"></span>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-[#c90068]">Role utilisateur</p>
            </div>

            @php($selectedRole = old('role', $user->role ?? 'member'))

            <fieldset>
                <legend class="mb-3 text-[13px] font-semibold text-[#374151]">
                    Choose the access level
                </legend>

                <div class="grid grid-cols-2 gap-1 rounded-full bg-[#efe2cd] p-1.5">
                    <div>
                        <input id="role-member" name="role" type="radio" value="member"
                            class="peer sr-only" @checked($selectedRole === 'member')>
                        <label for="role-member"
                            class="flex cursor-pointer items-center justify-center rounded-full px-4 py-3 text-[15px] font-bold text-[#80613c] transition peer-checked:bg-white peer-checked:text-[#c90068] peer-checked:shadow-[0_3px_8px_rgba(91,65,31,0.14)]">
                            Member
                        </label>
                    </div>

                    <div>
                        <input id="role-admin" name="role" type="radio" value="admin"
                            class="peer sr-only" @checked($selectedRole === 'admin')>
                        <label for="role-admin"
                            class="flex cursor-pointer items-center justify-center rounded-full px-4 py-3 text-[15px] font-bold text-[#80613c] transition peer-checked:bg-white peer-checked:text-[#c90068] peer-checked:shadow-[0_3px_8px_rgba(91,65,31,0.14)]">
                            Administrator
                        </label>
                    </div>
                </div>

                <p class="mt-3 text-[12px] leading-5 text-[#6b7280]">
                    An administrator can manage users. A member only accesses their own workspace.
                </p>
                <x-input-error class="mt-2" :messages="$errors->get('role')" />
            </fieldset>
        </section>
    @endif

    <section>
        <div class="mb-5 flex items-center gap-3">
            <span class="h-7 w-[3px] rounded-full bg-[#c50064]"></span>
            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-[#c50064]">Account access</p>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="password" class="mb-2 block text-[13px] font-semibold text-[#374151]">
                    {{ isset($user) ? 'New password' : 'Password' }}
                </label>
                <input id="password" name="password" type="password"
                    class="block w-full rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] px-4 py-4 text-[15px] text-[#0a0a0a] placeholder:text-[#94a3b8] transition focus:border-[#c50064] focus:bg-white focus:ring-4 focus:ring-[#c50064]/10"
                    placeholder="{{ isset($user) ? 'Laisser vide pour ne pas changer' : 'Password *' }}"
                    @required(! isset($user)) autocomplete="new-password">
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-[13px] font-semibold text-[#374151]">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                    class="block w-full rounded-[10px] border border-[#dce4ef] bg-[#f8fafc] px-4 py-4 text-[15px] text-[#0a0a0a] placeholder:text-[#94a3b8] transition focus:border-[#c50064] focus:bg-white focus:ring-4 focus:ring-[#c50064]/10"
                    placeholder="Confirm password *" @required(! isset($user)) autocomplete="new-password">
            </div>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 pt-1 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('users.index') }}" class="inline-flex items-center justify-center rounded-[10px] border border-[#dce4ef] bg-white px-5 py-4 text-[12px] font-extrabold uppercase tracking-[0.14em] text-[#374151] transition hover:bg-[#f8fafc]">
            Cancel
        </a>
        <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-[10px] bg-[#c50064] px-6 py-4 text-[13px] font-extrabold uppercase tracking-[0.08em] text-white shadow-[0_10px_24px_rgba(197,0,100,0.22)] transition hover:bg-[#c50064] focus:outline-none focus:ring-4 focus:ring-[#c50064]/20 sm:max-w-[420px]">
            {{ $submitLabel }}
        </button>
    </div>
</div>
