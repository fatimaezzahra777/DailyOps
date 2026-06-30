<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    protected $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([

            'task_id' => 'required|exists:tasks,id',
            'content' => 'required|string|max:5000',
            'author' => 'nullable|string|max:255',
        ]);

        $task = Task::with('project')->findOrFail($validated['task_id']);

        abort_unless($task->project?->isVisibleTo($request->user()), Response::HTTP_FORBIDDEN);

        $this->commentService->createComment($validated);

        return back()->with('success', 'Comment added successfully.');
    }

    public function destroy(Request $request, Comment $comment)
    {
        $comment->load('task.project');

        abort_unless($comment->task?->project?->canManageTasks($request->user()), Response::HTTP_FORBIDDEN);

        $this->commentService->deleteComment($comment->id);

        return back()->with('success', 'Comment deleted successfully.');
    }
}
