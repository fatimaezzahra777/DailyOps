<?php

namespace App\Http\Controllers;

use App\Services\CommentService;
use Illuminate\Http\Request;

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
            'content' => 'required|string',
            'author' => 'nullable|string|max:255',
        ]);

        $this->commentService->createComment($validated);

        return back()->with('success','Comment added successfully');
    }

    public function destroy($id)
    {
        $this->commentService->deleteComment($id);

        return back()->with('success','Comment deleted successfully');
    }
}