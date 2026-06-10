<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Project;
use App\Models\ProjectColumn;
use App\Models\User;
use App\Services\ProjectService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * Afficher toutes les projets 
     */
    public function index(Request $request)
    {
        $projects = $this->projectService->filterProjects($request);
        $allFilteredProjects = $this->projectService->getFilteredProjectCollection($request);
        $projectColumns = Schema::hasTable('project_columns')
            ? ProjectColumn::where('user_id', $request->user()->id)->orderBy('position')->orderBy('name')->get()
            : collect();

        return view('projects.index', compact('projects', 'allFilteredProjects', 'projectColumns'));
    }

    public function table(Request $request)
    {
        $projects = $this->projectService->filterProjects($request);
        $allFilteredProjects = $this->projectService->getFilteredProjectCollection($request);

        return view('projects.table', compact('projects', 'allFilteredProjects'));
    }

    public function gantt(Request $request)
    {
        $projects = $this->projectService->getFilteredProjectCollection($request);

        return view('projects.gantt', compact('projects'));
    }

    public function calendar(Request $request)
    {
        $projects = $this->projectService->getFilteredProjectCollection($request);
        $month = $this->resolveCalendarMonth($request);
        $calendarStart = $month->copy()->startOfMonth()->startOfWeek();
        $calendarEnd = $calendarStart->copy()->addDays(41)->endOfDay();
        $meetings = Meeting::query()
            ->visibleTo($request->user())
            ->with(['organizer', 'participants'])
            ->whereBetween('scheduled_at', [$calendarStart, $calendarEnd])
            ->orderBy('scheduled_at')
            ->get();
        $meetingParticipants = User::query()
            ->where('id', '!=', $request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('projects.calendar', compact('projects', 'month', 'meetings', 'meetingParticipants'));
    }

    public function reports(Request $request)
    {
        $projects = $this->projectService->getFilteredProjectCollection($request);

        return view('projects.reports', compact('projects'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Creer UN new Project 
     */
    public function store(Request $request)
    {
        $input = [
            'name' => $request->input('create_name', $request->input('name')),
            'description' => $request->input('create_description', $request->input('description')),
            'status' => $request->input('create_status', $request->input('status')),
            'start_date' => $request->input('create_start_date', $request->input('start_date')),
            'end_date' => $request->input('create_end_date', $request->input('end_date')),
        ];

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];

        if (Schema::hasTable('project_columns') && Schema::hasColumn('projects', 'column_id')) {
            $input['column_id'] = $request->input('create_column_id', $request->input('column_id'));
            $rules['column_id'] = [
                'nullable',
                Rule::exists('project_columns', 'id')->where('user_id', $request->user()->id),
            ];
        }

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'createProject')
                ->withInput()
                ->with('open_modal', 'create-project-modal');
        }

        $validated = $validator->validated();
        $validated['manager_id'] = $request->user()->id;

        $this->projectService->createProject($validated);

        return back()->with('success', 'Projet créé avec succès');
    }

    public function storeColumn(Request $request): RedirectResponse
    {
        abort_unless(Schema::hasTable('project_columns'), Response::HTTP_SERVICE_UNAVAILABLE);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:80',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'createColumn')
                ->withInput()
                ->with('open_modal', 'create-column-modal');
        }

        $validated = $validator->validated();

        ProjectColumn::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'position' => ProjectColumn::where('user_id', $request->user()->id)->max('position') + 1,
        ]);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Colonne ajoutée avec succès');
    }

    public function move(Request $request, Project $project)
    {
        $this->authorizeProjectManagement($request, $project);

        $rules = [
            'status' => 'required_without:column_id|nullable|in:pending,in_progress,completed',
        ];

        if (Schema::hasTable('project_columns') && Schema::hasColumn('projects', 'column_id')) {
            $rules['column_id'] = [
                'nullable',
                Rule::exists('project_columns', 'id')->where('user_id', $request->user()->id),
            ];
        }

        $validated = $request->validate($rules);
        $data = [];

        $supportsColumns = Schema::hasColumn('projects', 'column_id');

        if ($supportsColumns && array_key_exists('column_id', $validated) && filled($validated['column_id'])) {
            $data['column_id'] = $validated['column_id'];
        } else {
            if ($supportsColumns) {
                $data['column_id'] = null;
            }

            $data['status'] = $validated['status'] ?? 'pending';
        }

        $project->update($data);

        return response()->json([
            'message' => 'Project moved successfully.',
        ]);
    }

    /**
     * Display one project
     */
    public function show(Request $request, $id)
    {
        $project = Project::with([
            'manager',
            'collaborators' => fn ($query) => $query->orderBy('name'),
            'invitations' => fn ($query) => $query->latest(),
            'invitations.user',
            'tasks' => fn ($query) => $query->with('assignedUser')->latest(),
            'taskColumns' => fn ($query) => $query->with(['tasks.assignedUser'])->orderBy('position')->orderBy('name'),
        ])->findOrFail($id);
        $this->authorizeProjectAccess($request, $project);

        return view('projects.show', compact('project'));
    }

    /**
     * MOdifier un projet
    */

    public function edit(Request $request, $id)
    {
        $project = $this->projectService->getProjectById($id);
        $this->authorizeProjectManagement($request, $project);

        return view('projects.edit', compact('project'));
    }

    /**
     * Update project
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, "updateProject.{$id}")
                ->withInput()
                ->with('open_modal', "edit-project-modal-{$id}");
        }

        $validated = $validator->validated();
        $project = $this->projectService->getProjectById($id);
        $this->authorizeProjectManagement($request, $project);

        $this->projectService->updateProject($id, $validated);

        return back()->with('success', 'Projet mis à jour avec succès');
    }

    /**
     * Delete project
     */
    public function destroy(Request $request, $id)
    {
        $project = $this->projectService->getProjectById($id);
        $this->authorizeProjectManagement($request, $project);

        $this->projectService->deleteProject($id);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Projet supprimé avec succès');
    }

    protected function authorizeProjectAccess(Request $request, Project $project): void
    {
        abort_if(! $project->isVisibleTo($request->user()), Response::HTTP_FORBIDDEN);
    }

    protected function authorizeProjectManagement(Request $request, Project $project): void
    {
        abort_if(! $project->isManagedBy($request->user()), Response::HTTP_FORBIDDEN);
    }

    protected function resolveCalendarMonth(Request $request): Carbon
    {
        $month = $request->query('month');

        if (is_string($month) && preg_match('/^\d{4}-\d{2}$/', $month)) {
            return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        }

        return now()->startOfMonth();
    }
}
