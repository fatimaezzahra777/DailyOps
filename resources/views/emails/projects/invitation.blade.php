<x-mail::message>
# Invitation projet

{{ $manager?->name ?? 'Un manager' }} vous invite a collaborer sur le projet **{{ $project->name }}**.

@if ($project->description)
{{ $project->description }}
@endif

<x-mail::button :url="$acceptUrl">
Accepter l'invitation
</x-mail::button>

<x-mail::button :url="$declineUrl" color="error">
Refuser l'invitation
</x-mail::button>

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
