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
        <p class="font-medium text-red-700">Please fix the following errors:</p>
        <ul class="mt-2 space-y-1">
            @foreach ($errorsBag->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="{{ $prefix }}-name" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Name</label>
        <input id="{{ $prefix }}-name" name="{{ $inputName('name') }}" type="text" class="w-full px-4 py-3"
            value="{{ $fieldValue('name', $project?->name) }}" data-field-default="{{ $project?->name ?? '' }}"
            autocomplete="{{ $disableAutofill ? 'new-password' : 'off' }}" required>
    </div>

    <div>
        <label for="{{ $prefix }}-logo" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Logo du projet</label>
        <div class="project-logo-upload project-logo-upload-compact">
            @if ($project?->projectLogoUrl())
                <img src="{{ $project->projectLogoUrl() }}" alt="Logo de {{ $project->name }}"
                    class="project-logo-preview">
            @else
                <span class="project-logo-placeholder material-symbols-rounded" aria-hidden="true">image</span>
            @endif
            <div class="min-w-0 flex-1">
                <input id="{{ $prefix }}-logo" name="{{ $inputName('logo') }}" type="file"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="block w-full text-xs" data-project-logo-input>
                <p class="mt-1 text-[11px] text-[var(--muted)]">JPG, PNG ou WebP — 2 Mo max.</p>
            </div>
        </div>
        @if ($project?->logo_path)
            <label class="mt-2 inline-flex items-center gap-2 text-xs text-[var(--muted)]">
                <input name="remove_logo" type="checkbox" value="1" class="rounded border-[var(--line)] text-[var(--accent)]">
                Supprimer le logo actuel
            </label>
        @endif
    </div>

    <div class="md:col-span-2">
        <label for="{{ $prefix }}-description" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Description</label>
        <textarea id="{{ $prefix }}-description" name="{{ $inputName('description') }}" rows="4" class="w-full px-4 py-3"
            autocomplete="off" data-field-default="{{ $project?->description ?? '' }}">{{ $fieldValue('description', $project?->description) }}</textarea>
    </div>

    @include('projects.partials.company-selector', [
        'companyFieldName' => $inputName('company'),
        'companyPrefix' => $prefix,
        'selectedCompany' => $fieldValue('company', $project?->company),
    ])

    <div>
        <label for="{{ $prefix }}-status" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Status</label>
        <select id="{{ $prefix }}-status" name="{{ $inputName('status') }}" class="w-full px-4 py-3"
            autocomplete="off" data-field-default="{{ $project?->status ?? 'pending' }}">
            @foreach (['pending' => 'Pending', 'in_progress' => 'In progress', 'completed' => 'Completed'] as $value => $label)
                <option value="{{ $value }}" @selected($fieldValue('status', $project?->status ?? 'pending') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="{{ $prefix }}-start_date" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Start date</label>
        <input id="{{ $prefix }}-start_date" name="{{ $inputName('start_date') }}" type="date" class="w-full px-4 py-3"
            value="{{ $fieldValue('start_date', optional($project?->start_date)->format('Y-m-d')) }}"
            autocomplete="off" data-field-default="{{ optional($project?->start_date)->format('Y-m-d') }}">
    </div>

    <div>
        <label for="{{ $prefix }}-end_date" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">End date</label>
        <input id="{{ $prefix }}-end_date" name="{{ $inputName('end_date') }}" type="date" class="w-full px-4 py-3"
            value="{{ $fieldValue('end_date', optional($project?->end_date)->format('Y-m-d')) }}"
            autocomplete="off" data-field-default="{{ optional($project?->end_date)->format('Y-m-d') }}">
    </div>
</div>
