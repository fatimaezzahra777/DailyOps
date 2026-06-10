@php
    $meeting = $meeting ?? null;
    $prefix = $prefix ?? 'meeting';
    $useOldValues = $useOldValues ?? true;
    $selectedParticipants = $useOldValues
        ? old('meeting_participants', $meeting?->participants->pluck('id')->all() ?? [])
        : ($meeting?->participants->pluck('id')->all() ?? []);
    $fieldValue = fn (string $field, $default = '') => $useOldValues ? old($field, $default) : $default;
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="{{ $prefix }}-name" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Nom</label>
        <input id="{{ $prefix }}-name" name="meeting_name" type="text" class="w-full px-4 py-3"
            value="{{ $fieldValue('meeting_name', $meeting?->name) }}"
            data-field-default="{{ $meeting?->name ?? '' }}" required>
    </div>

    <div>
        <label for="{{ $prefix }}-title" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Titre</label>
        <input id="{{ $prefix }}-title" name="meeting_title" type="text" class="w-full px-4 py-3"
            value="{{ $fieldValue('meeting_title', $meeting?->title) }}"
            data-field-default="{{ $meeting?->title ?? '' }}" required>
    </div>

    <div class="md:col-span-2">
        <label for="{{ $prefix }}-url" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Lien de réunion</label>
        <input id="{{ $prefix }}-url" name="meeting_url" type="url" class="w-full px-4 py-3"
            value="{{ $fieldValue('meeting_url', $meeting?->meeting_url) }}"
            data-field-default="{{ $meeting?->meeting_url ?? '' }}"
            placeholder="https://meet.google.com/..." required>
    </div>

    <div class="md:col-span-2">
        <label for="{{ $prefix }}-scheduled-at" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Date et heure</label>
        <input id="{{ $prefix }}-scheduled-at" name="meeting_scheduled_at" type="datetime-local"
            class="w-full px-4 py-3"
            value="{{ $fieldValue('meeting_scheduled_at', $meeting?->scheduled_at?->format('Y-m-d\TH:i')) }}"
            data-field-default="{{ $meeting?->scheduled_at?->format('Y-m-d\TH:i') ?? '' }}" required>
    </div>

    <fieldset class="md:col-span-2">
        <legend class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Collaborateurs</legend>
        <div class="max-h-52 space-y-2 overflow-y-auto rounded-md border border-[var(--line)] bg-[var(--card-soft)] p-3">
            @forelse ($meetingParticipants as $participant)
                <label class="flex cursor-pointer items-center gap-3 rounded-md bg-white px-3 py-2 transition hover:bg-[#f8f8f8]">
                    <input name="meeting_participants[]" type="checkbox" value="{{ $participant->id }}"
                        @checked(in_array($participant->id, $selectedParticipants))
                        class="rounded border-[var(--line)] text-[var(--accent)] focus:ring-[var(--accent)]">
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium text-[var(--text-strong)]">{{ $participant->name }}</span>
                        <span class="block truncate text-xs text-[var(--muted)]">{{ $participant->email }}</span>
                    </span>
                </label>
            @empty
                <p class="text-sm text-[var(--muted)]">Aucun collaborateur disponible.</p>
            @endforelse
        </div>
    </fieldset>
</div>
