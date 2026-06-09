<x-mail::message>
<div style="text-align:center;margin-bottom:28px;">
    <img src="{{ asset('images/dailyops-logo.svg') }}" alt="{{ config('app.name') }}" style="max-width:320px;width:100%;height:auto;">
</div>

# Invitation a collaborer

Bonjour,

{{ $manager?->name ?? 'Un manager' }} vous invite a rejoindre le projet **{{ $project->name }}** sur {{ config('app.name') }}.

<div style="margin:24px 0;padding:18px 20px;border-left:4px solid #c90068;background:#f8fafc;border-radius:10px;">
    <p style="margin:0 0 6px;color:#6b7280;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;">Projet</p>
    <p style="margin:0;color:#111827;font-size:18px;font-weight:800;">{{ $project->name }}</p>
    @if ($project->description)
        <p style="margin:12px 0 0;color:#4b5563;line-height:1.6;">{{ $project->description }}</p>
    @endif
</div>

Vous pouvez accepter pour devenir collaborateur, ou refuser si cette invitation ne vous concerne pas.

<x-mail::button :url="$acceptUrl">
Accepter l'invitation
</x-mail::button>

<x-mail::button :url="$declineUrl" color="error">
Refuser l'invitation
</x-mail::button>

Ce lien expire dans 7 jours. Si vous n'attendiez pas cette invitation, vous pouvez ignorer cet email.

Merci,<br>
L'equipe DailyOps
</x-mail::message>
