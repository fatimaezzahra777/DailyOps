<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;
use Illuminate\View\View;

class MeetingController extends Controller
{
    public function index(Request $request): View
    {
        $meetings = Meeting::query()
            ->visibleTo($request->user())
            ->with(['organizer', 'participants'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhereHas('organizer', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('scheduled_at')
            ->paginate(12)
            ->withQueryString();

        $visibleMeetings = Meeting::query()->visibleTo($request->user());

        return view('meetings.index', [
            'meetings' => $meetings,
            'upcomingCount' => (clone $visibleMeetings)->where('scheduled_at', '>=', now())->count(),
            'organizedCount' => (clone $visibleMeetings)->where('organizer_id', $request->user()->id)->count(),
            'pastCount' => (clone $visibleMeetings)->where('scheduled_at', '<', now())->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = $this->meetingValidator($request);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'createMeeting')
                ->withInput()
                ->with('open_modal', 'create-project-modal')
                ->with('calendar_event_type', 'meeting');
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($request, $validated) {
            $meeting = Meeting::create([
                'organizer_id' => $request->user()->id,
                'name' => $validated['meeting_name'],
                'title' => $validated['meeting_title'],
                'meeting_url' => $validated['meeting_url'],
                'scheduled_at' => $validated['meeting_scheduled_at'],
            ]);

            $meeting->participants()->sync($validated['meeting_participants']);
        });

        return redirect()
            ->route('projects.calendar', [
                'month' => date('Y-m', strtotime($validated['meeting_scheduled_at'])),
            ])
            ->with('success', 'Meeting created successfully.');
    }

    public function update(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeOrganizer($request, $meeting);

        $validator = $this->meetingValidator($request);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, "updateMeeting.{$meeting->id}")
                ->withInput()
                ->with('open_modal', "edit-meeting-modal-{$meeting->id}");
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($meeting, $validated) {
            $meeting->update([
                'name' => $validated['meeting_name'],
                'title' => $validated['meeting_title'],
                'meeting_url' => $validated['meeting_url'],
                'scheduled_at' => $validated['meeting_scheduled_at'],
            ]);

            $meeting->participants()->sync($validated['meeting_participants']);
        });

        return redirect()
            ->route('projects.calendar', [
                'month' => date('Y-m', strtotime($validated['meeting_scheduled_at'])),
            ])
            ->with('success', 'Meeting updated successfully.');
    }

    public function destroy(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeOrganizer($request, $meeting);

        $month = $meeting->scheduled_at->format('Y-m');
        $meeting->delete();

        return redirect()
            ->route('projects.calendar', ['month' => $month])
            ->with('success', 'Meeting deleted successfully.');
    }

    private function meetingValidator(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'meeting_name' => ['required', 'string', 'max:255'],
            'meeting_title' => ['required', 'string', 'max:255'],
            'meeting_url' => ['required', 'url:http,https', 'max:2048'],
            'meeting_scheduled_at' => ['required', 'date'],
            'meeting_participants' => ['required', 'array', 'min:1'],
            'meeting_participants.*' => [
                'distinct',
                'integer',
                Rule::exists(User::class, 'id'),
                Rule::notIn([$request->user()->id]),
            ],
        ]);
    }

    private function authorizeOrganizer(Request $request, Meeting $meeting): void
    {
        abort_unless($meeting->isOrganizedBy($request->user()), 403);
    }
}
