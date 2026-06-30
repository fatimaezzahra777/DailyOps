@extends($isManager ? 'layouts.app' : 'layouts.support')

@section('content')
    @php
        $clientName = trim($conversation->first_name.' '.$conversation->last_name);
        $managerName = $conversation->project?->manager?->name ?? 'Project manager';
        $expiresSoon = $conversation->expires_at->isBefore(now()->addHours(6));
    @endphp

    <section class="support-chat-shell">
        <aside class="support-chat-sidebar">
            <div class="support-chat-client-card">
                <div class="flex items-center gap-3">
                    <div class="support-chat-avatar">
                        {{ strtoupper(substr($clientName ?: 'C', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-[var(--text-strong)]">{{ $clientName }}</p>
                        <p class="truncate text-xs text-[var(--muted)]">{{ $conversation->email }}</p>
                    </div>
                </div>

                @if ($conversation->phone)
                    <div class="mt-4 rounded-lg bg-white/70 px-3 py-2 text-xs text-[var(--muted)]">
                        <i class="ti ti-phone mr-1 text-[#c50064]"></i>
                        {{ $conversation->phone }}
                    </div>
                @endif
            </div>

            <div class="support-chat-meta-card">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[var(--muted)]">Project</p>
                <p class="mt-2 text-sm font-semibold text-[var(--text-strong)]">{{ $conversation->project?->name ?? 'Deleted project' }}</p>
                <p class="mt-1 text-xs text-[var(--muted)]">Manager: {{ $managerName }}</p>
            </div>

            <div class="support-chat-meta-card">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[var(--muted)]">Access window</p>
                <p class="mt-2 text-sm font-semibold {{ $expiresSoon ? 'text-[#dc2626]' : 'text-[#00a86b]' }}">
                    {{ $conversation->expires_at->format('d M Y · H:i') }}
                </p>
                <p class="mt-1 text-xs text-[var(--muted)]">This client chat expires automatically.</p>
            </div>
        </aside>

        <div class="support-chat-panel">
            <header class="support-chat-header">
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#c50064]">
                        {{ $isManager ? 'Manager conversation' : 'Client conversation' }}
                    </p>
                    <h1 class="mt-1 truncate font-['Syne'] text-xl font-extrabold text-[var(--text-strong)] sm:text-2xl">
                        {{ $conversation->title }}
                    </h1>
                </div>
                <span class="support-chat-status">
                    <span class="h-2 w-2 rounded-full {{ $conversation->isExpired() ? 'bg-[#dc2626]' : 'bg-[#00a86b]' }}"></span>
                    {{ $conversation->isExpired() ? 'Expired' : 'Open' }}
                </span>
            </header>

            <div class="support-chat-messages">
                @foreach ($conversation->messages as $message)
                    @php
                        $fromManager = $message->sender_type === \App\Models\SupportMessage::SENDER_MANAGER;
                        $isOwnMessage = $isManager ? $fromManager : ! $fromManager;
                    @endphp

                    <article class="support-message-row {{ $isOwnMessage ? 'support-message-row-own' : '' }}">
                        @unless ($isOwnMessage)
                            <div class="support-message-avatar">
                                {{ strtoupper(substr($message->sender_name ?: 'U', 0, 1)) }}
                            </div>
                        @endunless

                        <div class="support-message-bubble {{ $isOwnMessage ? 'support-message-bubble-own' : 'support-message-bubble-other' }}">
                            <div class="mb-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span class="text-xs font-bold">{{ $message->sender_name }}</span>
                                <span class="text-[11px] opacity-70">{{ $message->created_at->format('d M Y · H:i') }}</span>
                            </div>
                            <p class="whitespace-pre-line break-words text-sm leading-6">{{ $message->body }}</p>
                        </div>
                    </article>
                @endforeach
            </div>

            <form action="{{ $postRoute }}" method="POST" class="support-chat-composer">
                @csrf

                @if (session('success'))
                    <div class="mb-3 rounded-lg border border-[#00a86b]/20 bg-[#00a86b]/10 px-4 py-3 text-sm font-medium text-[#00a86b]">
                        {{ session('success') }}
                    </div>
                @endif

                <label for="body" class="sr-only">Message</label>
                <div class="flex items-end gap-3">
                    <textarea id="body" name="body" rows="1" class="support-chat-input"
                        placeholder="Write your message..." required>{{ old('body') }}</textarea>
                    <button type="submit" class="support-chat-send" aria-label="Send message">
                        <i class="ti ti-send text-lg"></i>
                    </button>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('body')" />
            </form>
        </div>
    </section>
@endsection
