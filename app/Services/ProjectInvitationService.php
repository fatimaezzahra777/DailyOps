<?php

namespace App\Services;

use App\Mail\ProjectInvitationMail;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class ProjectInvitationService
{
    public function sendInvitation(Project $project, ?string $assignedTo, ?User $manager): ?ProjectInvitation
    {
        $email = $this->resolveInviteEmail($assignedTo);

        if (! $email || $this->isManagerEmail($manager, $email)) {
            return null;
        }

        $invitation = ProjectInvitation::query()->updateOrCreate(
            [
                'project_id' => $project->id,
                'email' => $email,
                'status' => ProjectInvitation::STATUS_PENDING,
            ],
            [
                'invited_by_id' => $manager?->id,
                'token' => Str::random(64),
                'expires_at' => now()->addDays(7),
            ],
        );

        try {
            Mail::to($email)->send(new ProjectInvitationMail($invitation->load(['project', 'invitedBy'])));
        } catch (Throwable $exception) {
            Log::warning('Project invitation email could not be sent.', [
                'project_id' => $project->id,
                'email' => $email,
                'message' => $exception->getMessage(),
            ]);
        }

        return $invitation;
    }
    // transformer ce que le manager ecrit dans assigned_to en email 

    protected function resolveInviteEmail(?string $assignedTo): ?string
    {
        $value = trim((string) $assignedTo);

        if ($value === '') {
            return null;
        }
        // Si M ecrit Maryem,on cherche un user avec name = Maryem, puis récupère son email
        
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return mb_strtolower($value);
        }

        $normalized = mb_strtolower($value);

        return User::query()
            ->whereRaw('LOWER(name) = ?', [$normalized])
            ->orWhereRaw('LOWER(email) = ?', [$normalized])
            ->value('email');
    }

    protected function isManagerEmail(?User $manager, string $email): bool
    {
        return $manager && mb_strtolower($manager->email) === mb_strtolower($email);
    }
}
