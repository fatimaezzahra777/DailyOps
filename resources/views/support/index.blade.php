@extends('layouts.app')

@section('content')
    <section class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="font-['Syne'] text-2xl font-bold text-[var(--text-strong)]">Client support</h1>
                <p class="mt-1 text-sm text-[var(--muted)]">Conversations opened from the public DailyOps Support link.</p>
            </div>
            <a href="{{ route('support.create') }}" class="btn-secondary">
                <i class="ti ti-external-link mr-1"></i>
                Page client
            </a>
        </div>

        <div class="overflow-hidden rounded-md border border-[var(--line)] bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Project</th>
                            <th>Titre</th>
                            <th>Expiration</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($conversations as $conversation)
                            <tr>
                                <td>
                                    <div class="font-medium text-[var(--text-strong)]">{{ $conversation->first_name }} {{ $conversation->last_name }}</div>
                                    <div class="text-xs text-[var(--muted)]">{{ $conversation->email }}</div>
                                </td>
                                <td>{{ $conversation->project?->name ?? 'Project supprime' }}</td>
                                <td class="max-w-xs truncate">{{ $conversation->title }}</td>
                                <td>
                                    <span class="{{ $conversation->isExpired() ? 'text-[var(--red)]' : 'text-[var(--green)]' }}">
                                        {{ $conversation->expires_at->format('d/m/Y H:i') }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('support.manager.chat.show', $conversation) }}" class="btn-secondary inline-flex">
                                        <i class="ti ti-message-circle mr-1"></i>
                                        Open
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-[var(--muted)]">
                                    No support conversations yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $conversations->links() }}
    </section>
@endsection
