<?php

namespace App\Http\Controllers;

use App\Mail\ProjectInvitationMail;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ProjectInvitationController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        abort_if(! $project->isManagedBy($request->user()), Response::HTTP_FORBIDDEN);

        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'invite-collaborator-modal');
        }

        $email = Str::lower($validator->validated()['email']);
        $user = User::where('email', $email)->first();

        if ($user && $project->manager_id === $user->id) {
            return back()->withErrors([
                'email' => 'Le manager est deja responsable de ce projet.',
            ])->withInput()->with('open_modal', 'invite-collaborator-modal');
        }

        if ($user && $project->collaborators()->whereKey($user->id)->exists()) {
            return back()->with('success', 'Cette personne est deja collaborateur du projet.');
        }

        $invitation = ProjectInvitation::updateOrCreate(
            [
                'project_id' => $project->id,
                'email' => $email,
                'status' => ProjectInvitation::STATUS_PENDING,
            ],
            [
                'invited_by' => $request->user()->id,
                'user_id' => $user?->id,
                'token' => Str::random(64),
                'responded_at' => null,
            ],
        );

        Mail::to($email)->send(new ProjectInvitationMail($invitation->load(['project', 'inviter'])));

        return back()->with('success', 'Invitation envoyee par email.');
    }

    public function accept(Request $request, ProjectInvitation $invitation): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), Response::HTTP_FORBIDDEN);

        if (! $invitation->isPending()) {
            return redirect()
                ->route('projects.show', $invitation->project)
                ->with('success', 'Cette invitation a deja ete traitee.');
        }

        $user = $invitation->user ?: User::where('email', $invitation->email)->first();

        if (! $user) {
            return redirect()
                ->route('login')
                ->with('status', 'Creez ou connectez-vous avec cet email pour accepter l invitation.');
        }

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

        if (! auth()->check()) {
            return redirect()
                ->route('login')
                ->with('status', 'Invitation acceptee. Connectez-vous pour acceder au projet.');
        }

        return redirect()
            ->route('projects.show', $invitation->project)
            ->with('success', 'Invitation acceptee. Vous etes maintenant collaborateur du projet.');
    }

    public function decline(Request $request, ProjectInvitation $invitation): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), Response::HTTP_FORBIDDEN);

        if ($invitation->isPending()) {
            $invitation->update([
                'status' => ProjectInvitation::STATUS_DECLINED,
                'responded_at' => now(),
            ]);
        }

        return redirect()
            ->route('projects.index')
            ->with('success', 'Invitation refusee.');
    }
}
