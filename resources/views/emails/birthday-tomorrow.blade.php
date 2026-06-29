<x-mail::message>
# Birthday coming up

Hello {{ $recipient->name }},

**{{ $birthdayUser->name }}** has a birthday tomorrow.

Remember to wish them a happy birthday!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
