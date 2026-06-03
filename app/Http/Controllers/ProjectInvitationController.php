<?php

namespace App\Http\Controllers;

use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class ProjectInvitationController extends Controller
{
    public function accept(string $token): RedirectResponse
    {
        $invitation = $this->findPendingInvitation($token);
        $user = User::whereRaw('LOWER(email) = ?', [mb_strtolower($invitation->email)])->first();

        if (! $user) {
            return redirect()
                ->route('login')
                ->with('status', 'Creez ou connectez-vous avec cet email pour accepter l invitation.');
        }

        $invitation->project->collaborators()->syncWithoutDetaching([$user->id]);

        $invitation->update([
            'status' => ProjectInvitation::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        if (! auth()->check()) {
            return redirect()
                ->route('login')
                ->with('status', 'Invitation acceptee. Connectez-vous pour acceder au projet.');
        }

        return redirect()
            ->route('projects.show', $invitation->project)
            ->with('success', 'Invitation acceptee. Vous etes maintenant collaborateur du projet.');
    }

    public function decline(string $token): RedirectResponse
    {
        $invitation = $this->findPendingInvitation($token);

        $invitation->update([
            'status' => ProjectInvitation::STATUS_DECLINED,
            'declined_at' => now(),
        ]);

        return redirect()
            ->route('login')
            ->with('status', 'Invitation refusee.');
    }

    protected function findPendingInvitation(string $token): ProjectInvitation
    {
        $invitation = ProjectInvitation::with('project')
            ->where('token', $token)
            ->firstOrFail();

        abort_if(! $invitation->isPending(), Response::HTTP_GONE);

        return $invitation;
    }
}
