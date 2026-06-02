<?php

namespace Tests\Feature;

use App\Events\AssignmentNotificationSent;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AssignmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_assignment_broadcasts_to_assigned_user(): void
    {
        Event::fake([AssignmentNotificationSent::class]);

        $manager = User::factory()->create(['role' => 'admin']);
        $assignedUser = User::factory()->create([
            'email' => 'member@example.com',
        ]);
        $project = Project::create([
            'manager_id' => $manager->id,
            'name' => 'Launch Project',
            'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->post(route('tasks.store'), [
                'project_id' => $project->id,
                'title' => 'Prepare brief',
                'status' => 'todo',
                'priority' => 'medium',
                'assigned_to' => 'member@example.com',
            ])
            ->assertRedirect();

        Event::assertDispatched(
            AssignmentNotificationSent::class,
            fn (AssignmentNotificationSent $event) => $event->user->is($assignedUser)
                && $event->notification['type'] === 'task'
                && $event->notification['title'] === 'Nouvelle tâche assignée'
        );
    }

    public function test_project_assignment_broadcasts_to_assigned_user(): void
    {
        Event::fake([AssignmentNotificationSent::class]);

        $manager = User::factory()->create(['role' => 'admin']);
        $assignedUser = User::factory()->create([
            'name' => 'Assigned Member',
        ]);

        $this->actingAs($manager)
            ->post(route('projects.store'), [
                'name' => 'Website Refresh',
                'status' => 'pending',
                'assigned_to' => 'Assigned Member',
            ])
            ->assertRedirect();

        Event::assertDispatched(
            AssignmentNotificationSent::class,
            fn (AssignmentNotificationSent $event) => $event->user->is($assignedUser)
                && $event->notification['type'] === 'project'
                && $event->notification['title'] === 'Nouveau projet assigné'
        );
    }
}
