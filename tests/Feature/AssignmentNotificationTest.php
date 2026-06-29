<?php

namespace Tests\Feature;

use App\Events\AssignmentNotificationSent;
use App\Mail\TaskAssignedMail;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class AssignmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_assignment_broadcasts_to_assigned_user(): void
    {
        Event::fake([AssignmentNotificationSent::class]);
        Mail::fake();

        $manager = User::factory()->create(['role' => 'admin']);
        $assignedUser = User::factory()->create([
            'email' => 'member@example.com',
        ]);
        $project = Project::create([
            'manager_id' => $manager->id,
            'name' => 'Launch Project',
            'status' => 'pending',
        ]);
        $project->collaborators()->attach($assignedUser->id, [
            'invited_by' => $manager->id,
            'accepted_at' => now(),
        ]);

        $this->actingAs($manager)
            ->post(route('tasks.store'), [
                'project_id' => $project->id,
                'title' => 'Prepare brief',
                'status' => 'todo',
                'priority' => 'medium',
                'assigned_user_id' => $assignedUser->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Prepare brief',
            'project_id' => $project->id,
            'assigned_user_id' => $assignedUser->id,
            'assigned_to' => 'member@example.com',
        ]);

        Event::assertDispatched(
            AssignmentNotificationSent::class,
            fn (AssignmentNotificationSent $event) => $event->user->is($assignedUser)
                && $event->notification['type'] === 'task'
                && $event->notification['title'] === 'New task assigned'
        );

        Mail::assertSent(TaskAssignedMail::class, fn (TaskAssignedMail $mail) => $mail->task->assigned_user_id === $assignedUser->id);
    }

    public function test_task_cannot_be_assigned_to_a_user_outside_the_selected_project(): void
    {
        Mail::fake();

        $manager = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'manager_id' => $manager->id,
            'name' => 'Selected Project',
            'status' => 'pending',
        ]);
        $otherProject = Project::create([
            'manager_id' => $manager->id,
            'name' => 'Other Project',
            'status' => 'pending',
        ]);
        $otherCollaborator = User::factory()->create();

        $otherProject->collaborators()->attach($otherCollaborator->id, [
            'invited_by' => $manager->id,
            'accepted_at' => now(),
        ]);

        $this->actingAs($manager)
            ->post(route('tasks.store'), [
                'project_id' => $project->id,
                'title' => 'Restricted assignment',
                'status' => 'todo',
                'priority' => 'medium',
                'assigned_user_id' => $otherCollaborator->id,
            ])
            ->assertSessionHasErrors('assigned_user_id', errorBag: 'createTask');

        $this->assertDatabaseMissing('tasks', [
            'title' => 'Restricted assignment',
        ]);
    }

    public function test_realtime_notification_is_dispatched_when_assignment_email_fails(): void
    {
        Event::fake([AssignmentNotificationSent::class]);
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new RuntimeException('SMTP unavailable'));

        $manager = User::factory()->create(['role' => 'admin']);
        $assignedUser = User::factory()->create();
        $project = Project::create([
            'manager_id' => $manager->id,
            'name' => 'Resilient Notifications',
            'status' => 'pending',
        ]);
        $project->collaborators()->attach($assignedUser->id, [
            'invited_by' => $manager->id,
            'accepted_at' => now(),
        ]);

        $this->actingAs($manager)
            ->post(route('tasks.store'), [
                'project_id' => $project->id,
                'title' => 'Notify despite SMTP failure',
                'status' => 'todo',
                'priority' => 'medium',
                'assigned_user_id' => $assignedUser->id,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        Event::assertDispatched(
            AssignmentNotificationSent::class,
            fn (AssignmentNotificationSent $event) => $event->user->is($assignedUser)
                && $event->notification['message'] === 'Task "Notify despite SMTP failure" has been assigned to you.'
        );
    }

    public function test_project_creation_uses_manager_without_project_assignment_broadcast(): void
    {
        Event::fake([AssignmentNotificationSent::class]);

        $manager = User::factory()->create(['role' => 'admin']);

        $this->actingAs($manager)
            ->post(route('projects.store'), [
                'name' => 'Website Refresh',
                'company' => 'softart',
                'status' => 'pending',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'name' => 'Website Refresh',
            'company' => 'softart',
            'manager_id' => $manager->id,
            'assigned_to' => null,
        ]);
        Event::assertNotDispatched(AssignmentNotificationSent::class);
    }
}
