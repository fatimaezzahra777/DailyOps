@php
    $project = $project ?? null;
    $prefix = $prefix ?? 'project';
    $errorBag = $errorBag ?? 'default';
    $useOldValues = $useOldValues ?? true;
    $disableAutofill = $disableAutofill ?? false;
    $namePrefix = $namePrefix ?? '';
    $errorsBag = $errors->getBag($errorBag);
    $inputName = fn (string $field) => $namePrefix ? "{$namePrefix}{$field}" : $field;

    $fieldValue = function (string $field, $default = null) use ($useOldValues, $inputName) {
        return $useOldValues ? old($inputName($field), old($field, $default)) : $default;
    };
@endphp

@if ($errorsBag->any())
    <div class="rounded-md border border-red-600/20 bg-red-600/10 p-4 text-sm text-red-600">
        <p class="font-medium text-red-700">Veuillez corriger les erreurs suivantes :</p>
        <ul class="mt-2 space-y-1">
            @foreach ($errorsBag->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="{{ $prefix }}-name" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Nom</label>
        <input id="{{ $prefix }}-name" name="{{ $inputName('name') }}" type="text" class="w-full px-4 py-3"
            value="{{ $fieldValue('name', $project?->name) }}" data-field-default="{{ $project?->name ?? '' }}"
            autocomplete="{{ $disableAutofill ? 'new-password' : 'off' }}" required>
    </div>

    <div class="md:col-span-2">
        <label for="{{ $prefix }}-description" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Description</label>
        <textarea id="{{ $prefix }}-description" name="{{ $inputName('description') }}" rows="4" class="w-full px-4 py-3"
            autocomplete="off" data-field-default="{{ $project?->description ?? '' }}">{{ $fieldValue('description', $project?->description) }}</textarea>
    </div>

    <div>
        <label for="{{ $prefix }}-status" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Statut</label>
        <select id="{{ $prefix }}-status" name="{{ $inputName('status') }}" class="w-full px-4 py-3"
            autocomplete="off" data-field-default="{{ $project?->status ?? 'pending' }}">
            @foreach (['pending' => 'En attente', 'in_progress' => 'En cours', 'completed' => 'Terminé'] as $value => $label)
                <option value="{{ $value }}" @selected($fieldValue('status', $project?->status ?? 'pending') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="{{ $prefix }}-start_date" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Date de début</label>
        <input id="{{ $prefix }}-start_date" name="{{ $inputName('start_date') }}" type="date" class="w-full px-4 py-3"
            value="{{ $fieldValue('start_date', optional($project?->start_date)->format('Y-m-d')) }}"
            autocomplete="off" data-field-default="{{ optional($project?->start_date)->format('Y-m-d') }}">
    </div>

    <div>
        <label for="{{ $prefix }}-end_date" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Date de fin</label>
        <input id="{{ $prefix }}-end_date" name="{{ $inputName('end_date') }}" type="date" class="w-full px-4 py-3"
            value="{{ $fieldValue('end_date', optional($project?->end_date)->format('Y-m-d')) }}"
            autocomplete="off" data-field-default="{{ optional($project?->end_date)->format('Y-m-d') }}">
    </div>
</div>
