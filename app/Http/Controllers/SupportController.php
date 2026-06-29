<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function create(): View
    {
        return view('support.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:40',
            'title' => 'required|string|max:180',
            'description' => 'required|string|max:5000',
        ]);

        $email = Str::of($validated['email'])->trim()->lower()->toString();
        $project = Project::query()
            ->whereRaw('LOWER(client_email) = ?', [$email])
            ->whereNotNull('manager_id')
            ->latest()
            ->first();

        if (! $project) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'No DailyOps project matches this client email.',
                ]);
        }

        $conversation = DB::transaction(function () use ($validated, $email, $project) {
            $conversation = SupportConversation::create([
                'project_id' => $project->id,
                'manager_id' => $project->manager_id,
                'token' => Str::random(64),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $email,
                'phone' => $validated['phone'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'expires_at' => now()->addHours(48),
            ]);

            $conversation->messages()->create([
                'sender_type' => SupportMessage::SENDER_CLIENT,
                'sender_name' => $validated['first_name'].' '.$validated['last_name'],
                'body' => $validated['description'],
            ]);

            return $conversation;
        });

        return redirect()->route('support.chat.show', $conversation->token);
    }

    public function showClientChat(string $token): View
    {
        $conversation = SupportConversation::query()
            ->with(['project.manager', 'messages'])
            ->where('token', $token)
            ->firstOrFail();

        abort_if($conversation->isExpired(), Response::HTTP_GONE);

        return view('support.chat', [
            'conversation' => $conversation,
            'isManager' => false,
            'postRoute' => route('support.chat.messages.store', $conversation->token),
        ]);
    }

    public function storeClientMessage(Request $request, string $token): RedirectResponse
    {
        $conversation = SupportConversation::query()
            ->where('token', $token)
            ->firstOrFail();

        abort_if($conversation->isExpired(), Response::HTTP_GONE);

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $conversation->messages()->create([
            'sender_type' => SupportMessage::SENDER_CLIENT,
            'sender_name' => $conversation->first_name.' '.$conversation->last_name,
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Message sent.');
    }

    public function index(Request $request): View
    {
        $conversations = SupportConversation::query()
            ->with(['project', 'messages'])
            ->visibleTo($request->user())
            ->latest()
            ->paginate(12);

        return view('support.index', compact('conversations'));
    }

    public function showManagerChat(Request $request, SupportConversation $conversation): View
    {
        $this->authorizeManagerAccess($request, $conversation);

        $conversation->load(['project.manager', 'messages']);

        return view('support.chat', [
            'conversation' => $conversation,
            'isManager' => true,
            'postRoute' => route('support.manager.messages.store', $conversation),
        ]);
    }

    public function storeManagerMessage(Request $request, SupportConversation $conversation): RedirectResponse
    {
        $this->authorizeManagerAccess($request, $conversation);

        abort_if($conversation->isExpired(), Response::HTTP_GONE);

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $conversation->messages()->create([
            'user_id' => $request->user()->id,
            'sender_type' => SupportMessage::SENDER_MANAGER,
            'sender_name' => $request->user()->name,
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Reply sent.');
    }

    protected function authorizeManagerAccess(Request $request, SupportConversation $conversation): void
    {
        abort_if(
            ! $request->user()->isAdmin() && $conversation->manager_id !== $request->user()->id,
            Response::HTTP_FORBIDDEN
        );
    }
}
