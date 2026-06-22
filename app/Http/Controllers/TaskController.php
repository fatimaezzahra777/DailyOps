<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TaskColumn;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

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
        $projects = $this->availableProjects($request);
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
        $projects = $this->availableProjects(request());

        return view('tasks.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $input = [
            'project_id' => $request->input('create_project_id', $request->input('project_id')),
            'title' => $request->input('create_title', $request->input('title')),
            'description' => $request->input('create_description', $request->input('description')),
            'status' => $request->input('create_status', $request->input('status')),
            'task_column_id' => $request->input('create_task_column_id', $request->input('task_column_id')),
            'priority' => $request->input('create_priority', $request->input('priority')),
            'assigned_user_id' => $request->input('create_assigned_user_id', $request->input('assigned_user_id')),
            'due_date' => $request->input('create_due_date', $request->input('due_date')),
        ];

        $project = Project::with('collaborators')->find($input['project_id']);
        abort_if(! $project || ! $project->isManagedBy($request->user()), Response::HTTP_FORBIDDEN);

        $validator = Validator::make($input, [

            'project_id' => 'required|exists:projects,id',

            'title' => 'required|string|max:255',

            'description' => 'nullable|string',

            'status' => 'required|in:todo,in_progress,done',

            'task_column_id' => [
                'nullable',
                'integer',
                Rule::exists('task_columns', 'id')->where('project_id', $project->id),
            ],

            'priority' => 'required|in:low,medium,high',

            'assigned_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
                Rule::in($project->collaborators->pluck('id')->all()),
            ],

            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'createTask')
                ->withInput()
                ->with('open_modal', 'create-task-modal');
        }

        $validated = $validator->validated();
        if (filled($validated['task_column_id'] ?? null)) {
            $validated['status'] = 'todo';
        }
        $this->fillAssignedUserLabel($validated);

        $this->taskService->createTask($validated);

        return back()->with('success', 'Tâche créée avec succès.');
    }
    // afficher details d'une tache

    public function show($id)
    {
        $task = $this->taskService->getTaskById($id);
        abort_if(! $task->project?->isVisibleTo(request()->user()), Response::HTTP_FORBIDDEN);

        return view('tasks.show', compact('task'));
    }

    public function edit($id)
    {
        $task = $this->taskService->getTaskById($id);
        abort_if(! $task->project?->isManagedBy(request()->user()), Response::HTTP_FORBIDDEN);

        $projects = $this->availableProjects(request());

        return view('tasks.edit', compact('task', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [

            'project_id' => 'required|exists:projects,id',

            'title' => 'required|string|max:255',

            'description' => 'nullable|string',

            'status' => 'required|in:todo,in_progress,done',

            'task_column_id' => 'nullable|integer|exists:task_columns,id',

            'priority' => 'required|in:low,medium,high',

            'assigned_user_id' => 'nullable|integer|exists:users,id',

            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, "updateTask.{$id}")
                ->withInput()
                ->with('open_modal', "edit-task-modal-{$id}");
        }

        $validated = $validator->validated();
        $task = $this->taskService->getTaskById($id);
        abort_if(! $task->project?->isManagedBy($request->user()), Response::HTTP_FORBIDDEN);

        $project = Project::with('collaborators')->findOrFail($validated['project_id']);
        abort_if(! $project->isManagedBy($request->user()), Response::HTTP_FORBIDDEN);

        if (filled($validated['task_column_id']) && ! TaskColumn::where('project_id', $project->id)->whereKey($validated['task_column_id'])->exists()) {
            return back()
                ->withErrors(['task_column_id' => 'La colonne choisie doit appartenir au projet.'], "updateTask.{$id}")
                ->withInput()
                ->with('open_modal', "edit-task-modal-{$id}");
        }

        if (filled($validated['assigned_user_id']) && ! $project->collaborators->contains('id', (int) $validated['assigned_user_id'])) {
            return back()
                ->withErrors(['assigned_user_id' => 'La personne choisie doit etre collaborateur du projet.'], "updateTask.{$id}")
                ->withInput()
                ->with('open_modal', "edit-task-modal-{$id}");
        }

        $this->fillAssignedUserLabel($validated);

        $this->taskService->updateTask($id, $validated);

        return back()->with('success', 'Tâche mise à jour avec succès.');
    }

    public function destroy($id)
    {
        $task = $this->taskService->getTaskById($id);
        abort_if(! $task->project?->isManagedBy(request()->user()), Response::HTTP_FORBIDDEN);

        $this->taskService->deleteTask($id);

        return back()->with('success', 'Tâche supprimée avec succès.');
    }

    public function changeStatus(Request $request, $id)
    {
        $task = $this->taskService->getTaskById($id);
        abort_if(! $task->project?->isManagedBy($request->user()), Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'status' => 'nullable|in:todo,in_progress,done',
            'task_column_id' => [
                'nullable',
                'integer',
                Rule::exists('task_columns', 'id')->where('project_id', $task->project_id),
            ],
        ]);

        $this->taskService->changeStatus(
            $id,
            $validated['status'] ?? 'todo',
            $validated['task_column_id'] ?? null,
        );

        return response()->json([
            'success' => true
        ]);
    }

    protected function availableProjects(Request $request)
    {
        Project::archiveEligibleCompleted();

        return Project::query()
            ->active()
            ->with(['collaborators' => fn ($query) => $query->orderBy('name')])
            ->visibleTo($request->user())
            ->orderBy('name')
            ->get();
    }

    protected function fillAssignedUserLabel(array &$validated): void
    {
        $user = filled($validated['assigned_user_id'] ?? null)
            ? User::find($validated['assigned_user_id'])
            : null;

        $validated['assigned_to'] = $user?->email;
    }
}
