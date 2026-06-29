<x-mail::message>
<div style="text-align:center;margin-bottom:28px;">
    <img src="{{ asset('images/dailyops-logo.svg') }}" alt="{{ config('app.name') }}" style="max-width:320px;width:100%;height:auto;">
</div>

# Collaboration invitation

Hello,

{{ $manager?->name ?? 'A manager' }} invites you to join project **{{ $project->name }}** on {{ config('app.name') }}.

<div style="margin:24px 0;padding:18px 20px;border-left:4px solid #c50064;background:#f8fafc;border-radius:10px;">
    <p style="margin:0 0 6px;color:#6b7280;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;">Project</p>
    <p style="margin:0;color:#111827;font-size:18px;font-weight:800;">{{ $project->name }}</p>
    @if ($project->description)
        <p style="margin:12px 0 0;color:#4b5563;line-height:1.6;">{{ $project->description }}</p>
    @endif
</div>

You can accept to become a collaborator, or decline if this invitation is not for you.

<x-mail::button :url="$acceptUrl">
Accept invitation
</x-mail::button>

<x-mail::button :url="$declineUrl" color="error">
Decline invitation
</x-mail::button>

This link expires in 7 days. If you were not expecting this invitation, you can ignore this email.

Thanks,<br>
The DailyOps team
</x-mail::message>
