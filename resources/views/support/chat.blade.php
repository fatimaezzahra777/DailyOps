@extends($isManager ? 'layouts.app' : 'layouts.support')

@section('content')
    <section class="mx-auto flex w-full max-w-5xl flex-col gap-5">
        <div class="rounded-md border border-[var(--line)] bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--muted)]">Conversation support</p>
                    <h1 class="mt-2 break-words font-['Syne'] text-2xl font-bold text-[var(--text-strong)]">{{ $conversation->title }}</h1>
                    <p class="mt-2 text-sm text-[var(--text)]">
                        {{ $conversation->first_name }} {{ $conversation->last_name }} - {{ $conversation->email }}
                        @if ($conversation->phone)
                            - {{ $conversation->phone }}
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-[var(--muted)]">Project: {{ $conversation->project?->name ?? 'Project supprime' }}</p>
                </div>

                <div class="shrink-0 rounded-md border border-[var(--accent-line)] bg-[var(--accent-soft)] px-3 py-2 text-xs font-semibold text-[var(--accent)]">
                    Expires on {{ $conversation->expires_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>

        <div class="rounded-md border border-[var(--line)] bg-white shadow-sm">
            <div class="max-h-[58vh] space-y-4 overflow-y-auto p-4 sm:p-5">
                @foreach ($conversation->messages as $message)
                    @php($fromManager = $message->sender_type === \App\Models\SupportMessage::SENDER_MANAGER)
                    <article class="flex {{ $fromManager ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[min(38rem,88%)] rounded-md border px-4 py-3 {{ $fromManager ? 'border-[var(--accent-line)] bg-[var(--accent-soft)]' : 'border-[var(--line)] bg-[var(--card-soft)]' }}">
                            <div class="mb-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                                <span class="font-semibold text-[var(--text-strong)]">{{ $message->sender_name }}</span>
                                <span class="text-[var(--muted)]">{{ $message->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="whitespace-pre-line break-words text-sm leading-6 text-[var(--text)]">{{ $message->body }}</p>
                        </div>
                    </article>
                @endforeach
            </div>

            <form action="{{ $postRoute }}" method="POST" class="border-t border-[var(--line)] p-4 sm:p-5">
                @csrf

                @if (session('success'))
                    <div class="mb-4 rounded-md border border-[#00a86b]/20 bg-[#00a86b]/10 px-4 py-3 text-sm font-medium text-[#00a86b]">
                        {{ session('success') }}
                    </div>
                @endif

                <label for="body" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Message</label>
                <textarea id="body" name="body" rows="4" class="w-full px-4 py-3" required>{{ old('body') }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('body')" />

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn-primary">
                        <i class="ti ti-send mr-1"></i>
                        Envoyer
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection
