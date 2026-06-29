<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreadationFileManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_global_file_manager_grouped_by_folders(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'manager_id' => $user->id,
            'name' => 'Creative Project',
            'status' => 'pending',
        ]);
        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Design Assets',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'original_name' => 'brief.pdf',
            'path' => 'task-attachments/brief.pdf',
            'mime_type' => 'application/pdf',
            'size' => 2048,
        ]);

        TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'original_name' => 'mockup.png',
            'path' => 'task-attachments/mockup.png',
            'mime_type' => 'image/png',
            'size' => 4096,
        ]);

        $this->actingAs($user)
            ->get(route('creadations.index'))
            ->assertOk()
            ->assertSee('Créations')
            ->assertSee('PDFs')
            ->assertSee('Images')
            ->assertSee('brief.pdf')
            ->assertSee('mockup.png')
            ->assertSee('Creative Project')
            ->assertSee('Design Assets');
    }

    public function test_global_file_manager_only_shows_visible_project_files(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $visibleProject = Project::create([
            'manager_id' => $user->id,
            'name' => 'Visible Project',
            'status' => 'pending',
        ]);
        $hiddenProject = Project::create([
            'manager_id' => $otherUser->id,
            'name' => 'Hidden Project',
            'status' => 'pending',
        ]);

        $visibleTask = Task::create([
            'project_id' => $visibleProject->id,
            'title' => 'Visible Task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);
        $hiddenTask = Task::create([
            'project_id' => $hiddenProject->id,
            'title' => 'Hidden Task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        TaskAttachment::create([
            'task_id' => $visibleTask->id,
            'user_id' => $user->id,
            'original_name' => 'visible.pdf',
            'path' => 'task-attachments/visible.pdf',
            'mime_type' => 'application/pdf',
            'size' => 2048,
        ]);
        TaskAttachment::create([
            'task_id' => $hiddenTask->id,
            'user_id' => $otherUser->id,
            'original_name' => 'hidden.pdf',
            'path' => 'task-attachments/hidden.pdf',
            'mime_type' => 'application/pdf',
            'size' => 2048,
        ]);

        $this->actingAs($user)
            ->get(route('creadations.index'))
            ->assertOk()
            ->assertSee('visible.pdf')
            ->assertDontSee('hidden.pdf')
            ->assertDontSee('Hidden Project');
    }
}
