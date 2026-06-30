<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class TaskAttachmentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        abort_unless($task->project?->isVisibleTo($request->user()), Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'attachments' => ['required', 'array', 'max:8'],
            'attachments.*' => [
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,webp,gif,mp4,mov,webm,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,rar',
            ],
        ]);

        foreach ($validated['attachments'] as $file) {
            $path = $file->store("task-attachments/{$task->id}");

            $task->attachments()->create([
                'user_id' => $request->user()->id,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize() ?: 0,
            ]);
        }

        return back()->with('success', 'File added successfully');
    }

    public function download(Request $request, TaskAttachment $attachment)
    {
        $attachment->load('task.project');

        abort_unless($attachment->task?->project?->isVisibleTo($request->user()), Response::HTTP_FORBIDDEN);
        abort_unless(Storage::exists($attachment->path), Response::HTTP_NOT_FOUND);

        return Storage::download($attachment->path, $attachment->original_name);
    }

    public function preview(Request $request, TaskAttachment $attachment)
    {
        $attachment->load('task.project');

        abort_unless($attachment->task?->project?->isVisibleTo($request->user()), Response::HTTP_FORBIDDEN);
        abort_unless($attachment->isImage(), Response::HTTP_NOT_FOUND);
        abort_unless(Storage::exists($attachment->path), Response::HTTP_NOT_FOUND);

        return Storage::response($attachment->path, $attachment->original_name, [
            'Content-Type' => $attachment->mime_type ?: 'image/jpeg',
            'Content-Disposition' => 'inline; filename="'.$attachment->original_name.'"',
        ]);
    }

    public function destroy(Request $request, TaskAttachment $attachment)
    {
        $attachment->load('task.project');

        $canDelete = $attachment->task?->project?->canManageTasks($request->user())
            || $attachment->user_id === $request->user()->id;

        abort_unless($canDelete, Response::HTTP_FORBIDDEN);

        $attachment->delete();

        return back()->with('success', 'File deleted successfully');
    }
}
