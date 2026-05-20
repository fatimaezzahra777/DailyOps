<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(Request $request)
    {
        $tasks = $this->taskService->filterTasks($request);

        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $projects = Project::all();

        return view('tasks.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([

            'project_id' => 'required|exists:projects,id',

            'title' => 'required|string|max:255',

            'description' => 'nullable|string',

            'status' => 'required|in:todo,in_progress,done',

            'priority' => 'required|in:low,medium,high',

            'assigned_to' => 'nullable|string|max:255',

            'due_date' => 'nullable|date',
        ]);

        $this->taskService->createTask($validated);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully');
    }
    // afficher details d'une tache

    public function show($id)
    {
        $task = $this->taskService->getTaskById($id);

        return view('tasks.show', compact('task'));
    }

    public function edit($id)
    {
        $task = $this->taskService->getTaskById($id);

        $projects = Project::all();

        return view('tasks.edit', compact('task', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([

            'project_id' => 'required|exists:projects,id',

            'title' => 'required|string|max:255',

            'description' => 'nullable|string',

            'status' => 'required|in:todo,in_progress,done',

            'priority' => 'required|in:low,medium,high',

            'assigned_to' => 'nullable|string|max:255',

            'due_date' => 'nullable|date',
        ]);

        $this->taskService->updateTask($id, $validated);

        return redirect() ->route('tasks.index')->with('success', 'Task updated successfully');
    }

    public function destroy($id)
    {
        $this->taskService->deleteTask($id);

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully');
    }

    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:todo,in_progress,done'
        ]);

        $this->taskService->changeStatus($id,$request->status);

        return response()->json([
            'success' => true
        ]);
    }
}