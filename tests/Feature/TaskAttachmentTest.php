<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_visible_project_user_can_upload_task_attachment(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $project = Project::create([
            'manager_id' => $user->id,
            'name' => 'Attachment Project',
            'status' => 'pending',
        ]);
        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Attachment Task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        $this->actingAs($user)
            ->post(route('tasks.attachments.store', $task), [
                'attachments' => [
                    UploadedFile::fake()->create('brief.pdf', 120, 'application/pdf'),
                ],
            ])
            ->assertRedirect();

        $attachment = TaskAttachment::firstOrFail();

        $this->assertSame('brief.pdf', $attachment->original_name);
        $this->assertSame($task->id, $attachment->task_id);
        $this->assertSame($user->id, $attachment->user_id);
        Storage::disk('local')->assertExists($attachment->path);

        $this->actingAs($user)
            ->get(route('tasks.show', $task))
            ->assertOk()
            ->assertSee('brief.pdf')
            ->assertSee('Files');
    }

    public function test_task_attachment_can_be_downloaded_by_visible_user(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $project = Project::create([
            'manager_id' => $user->id,
            'name' => 'Download Project',
            'status' => 'pending',
        ]);
        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Download Task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);
        Storage::disk('local')->put('task-attachments/test-note.txt', 'hello');
        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'original_name' => 'note.txt',
            'path' => 'task-attachments/test-note.txt',
            'mime_type' => 'text/plain',
            'size' => 5,
        ]);

        $this->actingAs($user)
            ->get(route('task-attachments.download', $attachment))
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_attachment_owner_can_delete_attachment_and_file(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $project = Project::create([
            'manager_id' => $user->id,
            'name' => 'Delete Project',
            'status' => 'pending',
        ]);
        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Delete Task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);
        Storage::disk('local')->put('task-attachments/delete-me.txt', 'delete');
        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'original_name' => 'delete-me.txt',
            'path' => 'task-attachments/delete-me.txt',
            'mime_type' => 'text/plain',
            'size' => 6,
        ]);

        $this->actingAs($user)
            ->delete(route('task-attachments.destroy', $attachment))
            ->assertRedirect();

        $this->assertDatabaseMissing('task_attachments', [
            'id' => $attachment->id,
        ]);
        Storage::disk('local')->assertMissing('task-attachments/delete-me.txt');
    }
}
