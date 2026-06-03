@php
    $task = $task ?? null;
    $prefix = $prefix ?? 'task';
    $errorBag = $errorBag ?? 'default';
    $useOldValues = $useOldValues ?? true;
    $disableAutofill = $disableAutofill ?? false;
    $namePrefix = $namePrefix ?? '';
    $oldKeyPrefix = $oldKeyPrefix ?? '';
    $errorsBag = $errors->getBag($errorBag);
    $inputName = fn (string $field) => $namePrefix ? "{$namePrefix}{$field}" : $field;

    $oldFieldName = fn (string $field) => $oldKeyPrefix ? "{$oldKeyPrefix}{$field}" : $field;
    $fieldValue = function (string $field, $default = null) use ($useOldValues, $oldFieldName) {
        return $useOldValues ? old($oldFieldName($field), $default) : $default;
    };
    $defaultProjectId = $task->project_id ?? request('project_id', $projects->count() === 1 ? $projects->first()->id : '');
    $selectedProjectId = $fieldValue('project_id', $defaultProjectId);
    $defaultTaskColumnId = $task->task_column_id ?? '';
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
    <input type="hidden" name="{{ $inputName('task_column_id') }}" value="{{ $fieldValue('task_column_id', $defaultTaskColumnId) }}"
        data-task-column-field data-field-default="{{ $defaultTaskColumnId }}">

    <div class="md:col-span-2">
        <label for="{{ $prefix }}-title" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Task title</label>
        <input id="{{ $prefix }}-title" type="text" name="{{ $inputName('title') }}"
            value="{{ $fieldValue('title', $task->title ?? '') }}" placeholder="Enter a clear task title"
            class="w-full px-4 py-3" data-field-default="{{ $task->title ?? '' }}"
            autocomplete="{{ $disableAutofill ? 'new-password' : 'off' }}">
        @if ($errorsBag->has('title'))
            <p class="mt-2 text-sm text-rose-300">{{ $errorsBag->first('title') }}</p>
        @endif
    </div>

    <div>
        <label for="{{ $prefix }}-project" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Project</label>
        <select id="{{ $prefix }}-project" name="{{ $inputName('project_id') }}" class="w-full px-4 py-3"
            data-task-project-select
            data-field-default="{{ $defaultProjectId }}" autocomplete="off">
            <option value="">Select project</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" @selected($selectedProjectId == $project->id)>{{ $project->name }}</option>
            @endforeach
        </select>
        @if ($errorsBag->has('project_id'))
            <p class="mt-2 text-sm text-rose-300">{{ $errorsBag->first('project_id') }}</p>
        @endif
    </div>

    <div>
        <label for="{{ $prefix }}-status" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Status</label>
        <select id="{{ $prefix }}-status" name="{{ $inputName('status') }}" class="w-full px-4 py-3"
            data-field-default="{{ $task->status ?? 'todo' }}" autocomplete="off">
            <option value="todo" @selected($fieldValue('status', $task->status ?? 'todo') === 'todo')>Todo</option>
            <option value="in_progress" @selected($fieldValue('status', $task->status ?? '') === 'in_progress')>In progress</option>
            <option value="done" @selected($fieldValue('status', $task->status ?? '') === 'done')>Done</option>
        </select>
        @if ($errorsBag->has('status'))
            <p class="mt-2 text-sm text-rose-300">{{ $errorsBag->first('status') }}</p>
        @endif
    </div>

    <div>
        <label for="{{ $prefix }}-priority" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Priority</label>
        <select id="{{ $prefix }}-priority" name="{{ $inputName('priority') }}" class="w-full px-4 py-3"
            data-field-default="{{ $task->priority ?? 'low' }}" autocomplete="off">
            <option value="low" @selected($fieldValue('priority', $task->priority ?? 'low') === 'low')>Low</option>
            <option value="medium" @selected($fieldValue('priority', $task->priority ?? 'medium') === 'medium')>Medium</option>
            <option value="high" @selected($fieldValue('priority', $task->priority ?? '') === 'high')>High</option>
        </select>
        @if ($errorsBag->has('priority'))
            <p class="mt-2 text-sm text-rose-300">{{ $errorsBag->first('priority') }}</p>
        @endif
    </div>

    <div>
        <label for="{{ $prefix }}-assigned-to" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Assigned to</label>
        <select id="{{ $prefix }}-assigned-to" name="{{ $inputName('assigned_user_id') }}" class="w-full px-4 py-3"
            data-task-assignee-select data-field-default="{{ $task->assigned_user_id ?? '' }}" autocomplete="off">
            <option value="">Unassigned</option>
            @foreach ($projects as $project)
                @foreach ($project->collaborators as $collaborator)
                    <option value="{{ $collaborator->id }}" data-project-id="{{ $project->id }}"
                        @selected((string) $fieldValue('assigned_user_id', $task->assigned_user_id ?? '') === (string) $collaborator->id)>
                        {{ $collaborator->name }} — {{ $collaborator->email }}
                    </option>
                @endforeach
            @endforeach
        </select>
        <p class="mt-2 text-xs text-[var(--muted)]">Only accepted collaborators of the selected project are available.</p>
        @if ($errorsBag->has('assigned_user_id'))
            <p class="mt-2 text-sm text-rose-300">{{ $errorsBag->first('assigned_user_id') }}</p>
        @endif
    </div>

    <div>
        <label for="{{ $prefix }}-due-date" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Due date</label>
        <input id="{{ $prefix }}-due-date" type="date" name="{{ $inputName('due_date') }}"
            value="{{ $fieldValue('due_date', $task->due_date ?? '') }}" class="w-full px-4 py-3"
            data-field-default="{{ $task->due_date ?? '' }}" autocomplete="off">
        @if ($errorsBag->has('due_date'))
            <p class="mt-2 text-sm text-rose-300">{{ $errorsBag->first('due_date') }}</p>
        @endif
    </div>

    <div class="md:col-span-2">
        <label for="{{ $prefix }}-description" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Description</label>
        <textarea id="{{ $prefix }}-description" name="{{ $inputName('description') }}" rows="6"
            placeholder="Describe the task clearly for the team..." class="w-full px-4 py-3"
            data-field-default="{{ $task->description ?? '' }}" autocomplete="off">{{ $fieldValue('description', $task->description ?? '') }}</textarea>
        @if ($errorsBag->has('description'))
            <p class="mt-2 text-sm text-rose-300">{{ $errorsBag->first('description') }}</p>
        @endif
    </div>
</div>
