<x-mail::message>
# New Task Assigned

Hello {{ $assignee?->name }},

The manager of project **{{ $project?->name }}** assigned you task **{{ $task->title }}**.

@if ($task->due_date)
Due date: {{ \Illuminate\Support\Carbon::parse($task->due_date)->format('d/m/Y') }}
@endif

<x-mail::button :url="route('tasks.show', $task)">
View task
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
