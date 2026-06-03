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
use Illuminate\Validation\Rule;
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
                Rule::exists('users', 'email'),
            ],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'invite-collaborator-modal');
        }

        $validated = $validator->validated();
        $email = Str::lower($validated['email']);
        $user = User::where('email', $email)->firstOrFail();

        if ($project->manager_id === $user->id) {
            return back()->withErrors([
                'email' => 'Le manager est deja responsable de ce projet.',
            ])->withInput()->with('open_modal', 'invite-collaborator-modal');
        }

        if ($project->collaborators()->whereKey($user->id)->exists()) {
            return back()->with('success', 'Cette personne est deja collaborateur du projet.');
        }

        $invitation = ProjectInvitation::updateOrCreate(
            [
                'project_id' => $project->id,
                'email' => $email,
                'status' => 'pending',
            ],
            [
                'invited_by' => $request->user()->id,
                'user_id' => $user->id,
                'token' => Str::random(48),
                'responded_at' => null,
            ],
        );

        Mail::to($email)->send(new ProjectInvitationMail($invitation));

        return back()->with('success', 'Invitation envoyee par email.');
    }

    public function accept(Request $request, ProjectInvitation $invitation): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), Response::HTTP_FORBIDDEN);

        if (! $invitation->isPending()) {
            return redirect()->route('projects.show', $invitation->project)
                ->with('success', 'Cette invitation a deja ete traitee.');
        }

        $user = $invitation->user ?: User::where('email', $invitation->email)->first();

        abort_if(! $user, Response::HTTP_NOT_FOUND);

        $invitation->project->collaborators()->syncWithoutDetaching([
            $user->id => [
                'invited_by' => $invitation->invited_by,
                'accepted_at' => now(),
            ],
        ]);

        $invitation->update([
            'user_id' => $user->id,
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        return redirect()->route('projects.show', $invitation->project)
            ->with('success', 'Invitation acceptee. Vous etes maintenant collaborateur du projet.');
    }

    public function decline(Request $request, ProjectInvitation $invitation): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), Response::HTTP_FORBIDDEN);

        if ($invitation->isPending()) {
            $invitation->update([
                'status' => 'declined',
                'responded_at' => now(),
            ]);
        }

        return redirect()->route('projects.index')
            ->with('success', 'Invitation refusee.');
    }
}
