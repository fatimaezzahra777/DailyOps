<x-mail::message>
# Nouvelle tâche assignée

Bonjour {{ $assignee?->name }},

Le manager du projet **{{ $project?->name }}** vous a assigné la tâche **{{ $task->title }}**.

@if ($task->due_date)
Date limite: {{ \Illuminate\Support\Carbon::parse($task->due_date)->format('d/m/Y') }}
@endif

<x-mail::button :url="route('tasks.show', $task)">
Voir la tâche
</x-mail::button>

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
