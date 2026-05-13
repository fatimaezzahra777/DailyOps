<?php

namespace App\Http\Controllers;

use App\Services\ProjectService;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'assigned_to' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $this->projectService->createProject($validated);

        return redirect()->route('projects.index')->with('success', 'Projet créé avec succès');
    }

    /**
     * Display one project
     */
    public function show($id)
    {
        $project = $this->projectService->getProjectById($id);

        return view('projects.show', compact('project'));
    }

    /**
     * MOdifier un projet
    */

    public function edit($id)
    {
        $project = $this->projectService->getProjectById($id);

        return view('projects.edit', compact('project'));
    }

    /**
     * Update project
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'assigned_to' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $this->projectService->updateProject($id, $validated);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Projet mis à jour avec succès');
    }

    /**
     * Delete project
     */
    public function destroy($id)
    {
        $this->projectService->deleteProject($id);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Projet supprimé avec succès');
    }
}
