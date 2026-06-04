<?php

namespace Tests\Feature;

use App\Events\AssignmentNotificationSent;
use App\Mail\TaskAssignedMail;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
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
                && $event->notification['title'] === 'Nouvelle tâche assignée'
        );

        Mail::assertSent(TaskAssignedMail::class, fn (TaskAssignedMail $mail) => $mail->task->assigned_user_id === $assignedUser->id);
    }

    public function test_project_creation_uses_manager_without_project_assignment_broadcast(): void
    {
        Event::fake([AssignmentNotificationSent::class]);

        $manager = User::factory()->create(['role' => 'admin']);

        $this->actingAs($manager)
            ->post(route('projects.store'), [
                'name' => 'Website Refresh',
                'status' => 'pending',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'name' => 'Website Refresh',
            'manager_id' => $manager->id,
            'assigned_to' => null,
        ]);
        Event::assertNotDispatched(AssignmentNotificationSent::class);
    }
}
