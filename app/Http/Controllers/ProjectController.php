<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

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

        return view('projects.index', compact('projects', 'allFilteredProjects'));
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

        return view('projects.calendar', compact('projects'));
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
            'assigned_to' => $request->input('create_assigned_to', $request->input('assigned_to')),
            'start_date' => $request->input('create_start_date', $request->input('start_date')),
            'end_date' => $request->input('create_end_date', $request->input('end_date')),
        ];

        $validator = Validator::make($input, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'assigned_to' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'createProject')
                ->withInput()
                ->with('open_modal', 'create-project-modal');
        }

        $validated = $validator->validated();
        $validated['manager_id'] = $request->user()->id;
        $validated['assigned_to'] = $validated['assigned_to'] ?: $request->user()->name;

        $this->projectService->createProject($validated);

        return back()->with('success', 'Projet créé avec succès');
    }

    /**
     * Display one project
     */
    public function show(Request $request, $id)
    {
        $project = $this->projectService->getProjectById($id);
        $this->authorizeProjectAccess($request, $project);

        return view('projects.show', compact('project'));
    }

    /**
     * MOdifier un projet
    */

    public function edit(Request $request, $id)
    {
        $project = $this->projectService->getProjectById($id);
        $this->authorizeProjectAccess($request, $project);

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
            'assigned_to' => 'nullable|string|max:255',
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
        $this->authorizeProjectAccess($request, $project);

        $this->projectService->updateProject($id, $validated);

        return back()->with('success', 'Projet mis à jour avec succès');
    }

    /**
     * Delete project
     */
    public function destroy(Request $request, $id)
    {
        $project = $this->projectService->getProjectById($id);
        $this->authorizeProjectAccess($request, $project);

        $this->projectService->deleteProject($id);

        return back()->with('success', 'Projet supprimé avec succès');
    }

    protected function authorizeProjectAccess(Request $request, Project $project): void
    {
        if ($request->user()->isAdmin()) {
            return;
        }

        abort_if($project->manager_id !== $request->user()->id, Response::HTTP_FORBIDDEN);
    }
}
