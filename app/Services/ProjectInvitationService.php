<?php

namespace App\Services;

use App\Mail\ProjectInvitationMail;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class ProjectInvitationService
{
    public function acceptInvitation(ProjectInvitation $invitation, User $user): bool
    {
        if (
            ! $invitation->isPending()
            || strcasecmp($invitation->email, $user->email) !== 0
        ) {
            return false;
        }

        DB::transaction(function () use ($invitation, $user) {
            $invitation->project->collaborators()->syncWithoutDetaching([
                $user->id => [
                    'invited_by' => $invitation->invited_by,
                    'accepted_at' => now(),
                ],
            ]);

            $invitation->update([
                'user_id' => $user->id,
                'status' => ProjectInvitation::STATUS_ACCEPTED,
                'responded_at' => now(),
            ]);
        });

        return true;
    }

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
                'invited_by' => $manager?->id,
                'user_id' => User::where('email', $email)->value('id'),
                'token' => Str::random(64),
                'responded_at' => null,
            ],
        );

        try {
            Mail::to($email)->send(new ProjectInvitationMail($invitation->load(['project', 'inviter'])));
        } catch (Throwable $exception) {
            Log::warning('Project invitation email could not be sent.', [
                'project_id' => $project->id,
                'email' => $email,
                'message' => $exception->getMessage(),
            ]);
        }

        return $invitation;
    }
    protected function resolveInviteEmail(?string $assignedTo): ?string
    {
        $value = trim((string) $assignedTo);

        if ($value === '') {
            return null;
        }
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
