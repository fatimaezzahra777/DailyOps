<?php

namespace Tests\Feature;

use App\Mail\ProjectInvitationMail;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProjectInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_assignment_sends_invitation_email(): void
    {
        Mail::fake();

        $manager = User::factory()->create(['role' => 'admin']);
        $collaborator = User::factory()->create(['email' => 'collab@example.com']);

        $this->actingAs($manager)
            ->post(route('projects.store'), [
                'name' => 'Client Launch',
                'status' => 'pending',
                'assigned_to' => 'collab@example.com',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('project_invitations', [
            'email' => 'collab@example.com',
            'status' => ProjectInvitation::STATUS_PENDING,
            'invited_by_id' => $manager->id,
        ]);

        Mail::assertSent(
            ProjectInvitationMail::class,
            fn (ProjectInvitationMail $mail) => $mail->hasTo($collaborator->email)
        );
    }

    public function test_accepting_project_invitation_adds_collaborator(): void
    {
        Mail::fake();

        $manager = User::factory()->create(['role' => 'admin']);
        $collaborator = User::factory()->create(['email' => 'collab@example.com']);

        $this->actingAs($manager)
            ->post(route('projects.store'), [
                'name' => 'Client Launch',
                'status' => 'pending',
                'assigned_to' => 'collab@example.com',
            ]);

        $invitation = ProjectInvitation::firstOrFail();
        $project = Project::firstOrFail();

        $this->actingAs($collaborator)
            ->get(route('projects.show', $project))
            ->assertForbidden();

        auth()->logout();

        $this->get(route('project-invitations.accept', $invitation->token))
            ->assertRedirect();

        $this->assertDatabaseHas('project_collaborators', [
            'project_id' => $project->id,
            'user_id' => $collaborator->id,
        ]);
        $this->assertDatabaseHas('project_invitations', [
            'id' => $invitation->id,
            'status' => ProjectInvitation::STATUS_ACCEPTED,
        ]);

        $this->actingAs($collaborator)
            ->get(route('projects.show', $project))
            ->assertOk();
    }

    public function test_declining_project_invitation_does_not_add_collaborator(): void
    {
        Mail::fake();

        $manager = User::factory()->create(['role' => 'admin']);
        $collaborator = User::factory()->create(['email' => 'collab@example.com']);

        $this->actingAs($manager)
            ->post(route('projects.store'), [
                'name' => 'Client Launch',
                'status' => 'pending',
                'assigned_to' => 'collab@example.com',
            ]);

        $invitation = ProjectInvitation::firstOrFail();

        $this->get(route('project-invitations.decline', $invitation->token))
            ->assertRedirect(route('login'));

        $project = Project::firstOrFail();

        $this->assertDatabaseMissing('project_collaborators', [
            'project_id' => $project->id,
            'user_id' => $collaborator->id,
        ]);
        $this->assertDatabaseHas('project_invitations', [
            'id' => $invitation->id,
            'status' => ProjectInvitation::STATUS_DECLINED,
        ]);
    }
}
