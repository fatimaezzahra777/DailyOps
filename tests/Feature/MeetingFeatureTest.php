<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_meeting_with_participants(): void
    {
        $organizer = User::factory()->create();
        $participants = User::factory()->count(2)->create();

        $this->actingAs($organizer)
            ->post(route('meetings.store'), [
                'meeting_name' => 'Daily sync',
                'meeting_title' => 'Planning de la semaine',
                'meeting_url' => 'https://meet.google.com/abc-defg-hij',
                'meeting_scheduled_at' => '2026-06-18T10:30',
                'meeting_participants' => $participants->pluck('id')->all(),
            ])
            ->assertRedirect(route('projects.calendar', ['month' => '2026-06']));

        $meeting = Meeting::where('title', 'Planning de la semaine')->firstOrFail();

        $this->assertSame($organizer->id, $meeting->organizer_id);
        $this->assertEqualsCanonicalizing(
            $participants->pluck('id')->all(),
            $meeting->participants()->pluck('users.id')->all(),
        );
    }

    public function test_meeting_requires_a_valid_link_date_and_participant(): void
    {
        $organizer = User::factory()->create();

        $this->actingAs($organizer)
            ->from(route('projects.calendar'))
            ->post(route('meetings.store'), [
                'meeting_name' => 'Invalid meeting',
                'meeting_title' => 'Missing information',
                'meeting_url' => 'not-a-link',
                'meeting_scheduled_at' => '',
                'meeting_participants' => [],
            ])
            ->assertRedirect(route('projects.calendar'))
            ->assertSessionHasErrors([
                'meeting_url',
                'meeting_scheduled_at',
                'meeting_participants',
            ], errorBag: 'createMeeting')
            ->assertSessionHas('open_modal', 'create-project-modal')
            ->assertSessionHas('calendar_event_type', 'meeting');

        $this->assertDatabaseCount('meetings', 0);
    }

    public function test_meeting_is_visible_only_to_organizer_and_participants(): void
    {
        $organizer = User::factory()->create();
        $participant = User::factory()->create();
        $otherUser = User::factory()->create();
        $meeting = Meeting::create([
            'organizer_id' => $organizer->id,
            'name' => 'Private sync',
            'title' => 'Visible calendar meeting',
            'meeting_url' => 'https://zoom.us/j/123456789',
            'scheduled_at' => now()->startOfMonth()->addDays(4)->setTime(14, 0),
        ]);
        $meeting->participants()->attach($participant);

        $this->actingAs($organizer)
            ->get(route('projects.calendar'))
            ->assertOk()
            ->assertSee('Visible calendar meeting');

        $this->actingAs($participant)
            ->get(route('projects.calendar'))
            ->assertOk()
            ->assertSee('Visible calendar meeting');

        $this->actingAs($otherUser)
            ->get(route('projects.calendar'))
            ->assertOk()
            ->assertDontSee('Visible calendar meeting');
    }

    public function test_calendar_contains_project_and_meeting_forms(): void
    {
        $user = User::factory()->create();
        $participant = User::factory()->create([
            'name' => 'Calendar Collaborator',
        ]);

        $this->actingAs($user)
            ->get(route('projects.calendar'))
            ->assertOk()
            ->assertSee('data-calendar-event-type="project"', false)
            ->assertSee('data-calendar-event-type="meeting"', false)
            ->assertSee('action="'.route('projects.store').'"', false)
            ->assertSee('action="'.route('meetings.store').'"', false)
            ->assertSee('Calendar Collaborator');
    }

    public function test_organizer_can_update_a_meeting_and_its_participants(): void
    {
        $organizer = User::factory()->create();
        $oldParticipant = User::factory()->create();
        $newParticipant = User::factory()->create();
        $meeting = Meeting::create([
            'organizer_id' => $organizer->id,
            'name' => 'Old name',
            'title' => 'Old title',
            'meeting_url' => 'https://meet.google.com/old-link',
            'scheduled_at' => '2026-06-12 09:00:00',
        ]);
        $meeting->participants()->attach($oldParticipant);

        $this->actingAs($organizer)
            ->put(route('meetings.update', $meeting), [
                'meeting_name' => 'Weekly sync',
                'meeting_title' => 'Updated planning',
                'meeting_url' => 'https://zoom.us/j/987654321',
                'meeting_scheduled_at' => '2026-07-02T15:30',
                'meeting_participants' => [$newParticipant->id],
            ])
            ->assertRedirect(route('projects.calendar', ['month' => '2026-07']));

        $this->assertDatabaseHas('meetings', [
            'id' => $meeting->id,
            'name' => 'Weekly sync',
            'title' => 'Updated planning',
            'meeting_url' => 'https://zoom.us/j/987654321',
        ]);
        $this->assertDatabaseMissing('meeting_user', [
            'meeting_id' => $meeting->id,
            'user_id' => $oldParticipant->id,
        ]);
        $this->assertDatabaseHas('meeting_user', [
            'meeting_id' => $meeting->id,
            'user_id' => $newParticipant->id,
        ]);
    }

    public function test_organizer_can_delete_a_meeting(): void
    {
        $organizer = User::factory()->create();
        $participant = User::factory()->create();
        $meeting = Meeting::create([
            'organizer_id' => $organizer->id,
            'name' => 'Disposable meeting',
            'title' => 'Delete me',
            'meeting_url' => 'https://meet.google.com/delete-me',
            'scheduled_at' => '2026-06-20 11:00:00',
        ]);
        $meeting->participants()->attach($participant);

        $this->actingAs($organizer)
            ->delete(route('meetings.destroy', $meeting))
            ->assertRedirect(route('projects.calendar', ['month' => '2026-06']));

        $this->assertDatabaseMissing('meetings', ['id' => $meeting->id]);
        $this->assertDatabaseMissing('meeting_user', ['meeting_id' => $meeting->id]);
    }

    public function test_participant_cannot_update_or_delete_a_meeting(): void
    {
        $organizer = User::factory()->create();
        $participant = User::factory()->create();
        $meeting = Meeting::create([
            'organizer_id' => $organizer->id,
            'name' => 'Protected meeting',
            'title' => 'Organizer only',
            'meeting_url' => 'https://meet.google.com/protected',
            'scheduled_at' => now()->addDay(),
        ]);
        $meeting->participants()->attach($participant);

        $this->actingAs($participant)
            ->put(route('meetings.update', $meeting), [
                'meeting_name' => 'Hacked',
                'meeting_title' => 'Hacked',
                'meeting_url' => 'https://example.com/hacked',
                'meeting_scheduled_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'meeting_participants' => [$organizer->id],
            ])
            ->assertForbidden();

        $this->actingAs($participant)
            ->delete(route('meetings.destroy', $meeting))
            ->assertForbidden();

        $this->assertDatabaseHas('meetings', [
            'id' => $meeting->id,
            'title' => 'Organizer only',
        ]);
    }

    public function test_calendar_shows_management_actions_only_to_organizer(): void
    {
        $organizer = User::factory()->create();
        $participant = User::factory()->create();
        $meeting = Meeting::create([
            'organizer_id' => $organizer->id,
            'name' => 'CRUD meeting',
            'title' => 'Management controls',
            'meeting_url' => 'https://meet.google.com/crud-test',
            'scheduled_at' => now()->startOfMonth()->addDays(2),
        ]);
        $meeting->participants()->attach($participant);

        $this->actingAs($organizer)
            ->get(route('projects.calendar'))
            ->assertOk()
            ->assertSee("edit-meeting-modal-{$meeting->id}")
            ->assertSee("delete-meeting-modal-{$meeting->id}");

        $this->actingAs($participant)
            ->get(route('projects.calendar'))
            ->assertOk()
            ->assertSee("meeting-details-modal-{$meeting->id}")
            ->assertDontSee("edit-meeting-modal-{$meeting->id}")
            ->assertDontSee("delete-meeting-modal-{$meeting->id}");
    }

    public function test_meetings_index_lists_only_visible_meetings(): void
    {
        $user = User::factory()->create();
        $organizer = User::factory()->create();
        $otherUser = User::factory()->create();

        Meeting::create([
            'organizer_id' => $user->id,
            'name' => 'Owned meeting',
            'title' => 'My organized meeting',
            'meeting_url' => 'https://meet.google.com/owned',
            'scheduled_at' => now()->addDay(),
        ]);

        $participatingMeeting = Meeting::create([
            'organizer_id' => $organizer->id,
            'name' => 'Shared meeting',
            'title' => 'My participant meeting',
            'meeting_url' => 'https://meet.google.com/shared',
            'scheduled_at' => now()->addDays(2),
        ]);
        $participatingMeeting->participants()->attach($user);

        Meeting::create([
            'organizer_id' => $otherUser->id,
            'name' => 'Private meeting',
            'title' => 'Hidden meeting',
            'meeting_url' => 'https://meet.google.com/hidden',
            'scheduled_at' => now()->addDays(3),
        ]);

        $this->actingAs($user)
            ->get(route('meetings.index'))
            ->assertOk()
            ->assertSee('All meetings')
            ->assertSee('My organized meeting')
            ->assertSee('My participant meeting')
            ->assertDontSee('Hidden meeting')
            ->assertSee('Meetings');
    }

    public function test_meetings_index_can_be_searched(): void
    {
        $user = User::factory()->create();

        Meeting::create([
            'organizer_id' => $user->id,
            'name' => 'Daily team',
            'title' => 'Sprint planning',
            'meeting_url' => 'https://meet.google.com/sprint',
            'scheduled_at' => now()->addDay(),
        ]);
        Meeting::create([
            'organizer_id' => $user->id,
            'name' => 'Client sync',
            'title' => 'Design review',
            'meeting_url' => 'https://meet.google.com/design',
            'scheduled_at' => now()->addDays(2),
        ]);

        $this->actingAs($user)
            ->get(route('meetings.index', ['search' => 'Sprint']))
            ->assertOk()
            ->assertSee('Sprint planning')
            ->assertDontSee('Design review');
    }

    public function test_guest_cannot_open_meetings_index(): void
    {
        $this->get(route('meetings.index'))
            ->assertRedirect(route('login'));
    }
}
