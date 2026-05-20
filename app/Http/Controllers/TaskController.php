<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $projects = Project::orderBy('name')->get();
        $openModal = session('open_modal');

        if ($request->ajax()) {

            return response()->json([
                'results' => view('tasks.partials.results', compact('tasks', 'projects', 'openModal'))->render(),
                'pagination' => view('tasks.partials.pagination', compact('tasks'))->render(),
            ]);
        }

        return view('tasks.index', compact('tasks', 'projects'));
    }


    public function create()
    {
        $projects = Project::all();

        return view('tasks.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $input = [
            'project_id' => $request->input('create_project_id', $request->input('project_id')),
            'title' => $request->input('create_title', $request->input('title')),
            'description' => $request->input('create_description', $request->input('description')),
            'status' => $request->input('create_status', $request->input('status')),
            'priority' => $request->input('create_priority', $request->input('priority')),
            'assigned_to' => $request->input('create_assigned_to', $request->input('assigned_to')),
            'due_date' => $request->input('create_due_date', $request->input('due_date')),
        ];

        $validator = Validator::make($input, [

            'project_id' => 'required|exists:projects,id',

            'title' => 'required|string|max:255',

            'description' => 'nullable|string',

            'status' => 'required|in:todo,in_progress,done',

            'priority' => 'required|in:low,medium,high',

            'assigned_to' => 'nullable|string|max:255',

            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'createTask')
                ->withInput()
                ->with('open_modal', 'create-task-modal');
        }

        $validated = $validator->validated();

        $this->taskService->createTask($validated);

        return back()->with('success', 'Task created successfully');
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
        $validator = Validator::make($request->all(), [

            'project_id' => 'required|exists:projects,id',

            'title' => 'required|string|max:255',

            'description' => 'nullable|string',

            'status' => 'required|in:todo,in_progress,done',

            'priority' => 'required|in:low,medium,high',

            'assigned_to' => 'nullable|string|max:255',

            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, "updateTask.{$id}")
                ->withInput()
                ->with('open_modal', "edit-task-modal-{$id}");
        }

        $validated = $validator->validated();

        $this->taskService->updateTask($id, $validated);

        return back()->with('success', 'Task updated successfully');
    }

    public function destroy($id)
    {
        $this->taskService->deleteTask($id);

        return back()->with('success', 'Task deleted successfully');
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
