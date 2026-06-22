<x-mail::message>
# Un anniversaire approche

Bonjour {{ $recipient->name }},

L’anniversaire de **{{ $birthdayUser->name }}** est demain.

Pensez à lui souhaiter un joyeux anniversaire !

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
