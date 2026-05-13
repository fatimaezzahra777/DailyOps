@php
    $project = $project ?? null;
    $prefix = $prefix ?? 'project';
    $errorBag = $errorBag ?? 'default';
    $useOldValues = $useOldValues ?? true;
    $errorsBag = $errors->getBag($errorBag);

    $fieldValue = function (string $field, $default = null) use ($useOldValues) {
        return $useOldValues ? old($field, $default) : $default;
    };
@endphp

@if ($errorsBag->any())
    <div class="rounded-2xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-200">
        <p class="font-medium text-rose-100">Please fix the following errors:</p>
        <ul class="mt-2 space-y-1">
            @foreach ($errorsBag->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="{{ $prefix }}-name" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Name</label>
        <input id="{{ $prefix }}-name" name="name" type="text" class="w-full px-4 py-3"
            value="{{ $fieldValue('name', $project?->name) }}" required>
    </div>

    <div class="md:col-span-2">
        <label for="{{ $prefix }}-description" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Description</label>
        <textarea id="{{ $prefix }}-description" name="description" rows="4" class="w-full px-4 py-3">{{ $fieldValue('description', $project?->description) }}</textarea>
    </div>

    <div>
        <label for="{{ $prefix }}-status" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Status</label>
        <select id="{{ $prefix }}-status" name="status" class="w-full px-4 py-3">
            @foreach (['pending' => 'Pending', 'in_progress' => 'In progress', 'completed' => 'Completed'] as $value => $label)
                <option value="{{ $value }}" @selected($fieldValue('status', $project?->status ?? 'pending') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="{{ $prefix }}-assigned_to" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Assigned to</label>
        <input id="{{ $prefix }}-assigned_to" name="assigned_to" type="text" class="w-full px-4 py-3"
            value="{{ $fieldValue('assigned_to', $project?->assigned_to) }}">
    </div>

    <div>
        <label for="{{ $prefix }}-start_date" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Start date</label>
        <input id="{{ $prefix }}-start_date" name="start_date" type="date" class="w-full px-4 py-3"
            value="{{ $fieldValue('start_date', optional($project?->start_date)->format('Y-m-d')) }}">
    </div>

    <div>
        <label for="{{ $prefix }}-end_date" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">End date</label>
        <input id="{{ $prefix }}-end_date" name="end_date" type="date" class="w-full px-4 py-3"
            value="{{ $fieldValue('end_date', optional($project?->end_date)->format('Y-m-d')) }}">
    </div>
</div>
