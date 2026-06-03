<x-mail::message>
# Invitation projet

{{ $inviter->name }} vous invite a collaborer sur le projet **{{ $project->name }}**.

Pour devenir collaborateur de ce projet, acceptez l'invitation ci-dessous.

<x-mail::button :url="$acceptUrl">
Accepter l'invitation
</x-mail::button>

<x-mail::button :url="$declineUrl" color="error">
Refuser
</x-mail::button>

Ce lien expire dans 7 jours.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
