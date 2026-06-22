<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ProjectInvitation;
use App\Models\User;
use App\Services\ProjectInvitationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly ProjectInvitationService $invitationService,
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $user = User::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'birth_date' => $request->date('birth_date'),
            'password' => Hash::make($request->string('password')),
            'role' => 'member',
        ]);

        event(new Registered($user));

        Auth::login($user);

        $invitation = ProjectInvitation::find(
            $request->session()->get('pending_project_invitation_id')
        );

        if ($invitation && $this->invitationService->acceptInvitation($invitation, $user)) {
            $request->session()->forget('pending_project_invitation_id');

            return redirect()
                ->route('projects.show', $invitation->project)
                ->with('success', 'Invitation acceptee. Vous etes maintenant collaborateur du projet.');
        }

        return redirect(route('dashboard', absolute: false));
    }
}
