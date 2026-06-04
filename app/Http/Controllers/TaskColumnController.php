<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TaskColumn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskColumnController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        abort_if(! $project->isManagedBy($request->user()), Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'name' => 'required|string|max:80',
        ]);

        $project->taskColumns()->create([
            'name' => $validated['name'],
            'position' => $project->taskColumns()->max('position') + 1,
        ]);

        return back()->with('success', 'Colonne ajoutee avec succes.');
    }

    public function update(Request $request, TaskColumn $taskColumn): RedirectResponse
    {
        abort_if(! $taskColumn->project->isManagedBy($request->user()), Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'name' => 'required|string|max:80',
        ]);

        $taskColumn->update($validated);

        return back()->with('success', 'Colonne mise a jour avec succes.');
    }

    public function destroy(Request $request, TaskColumn $taskColumn): RedirectResponse
    {
        abort_if(! $taskColumn->project->isManagedBy($request->user()), Response::HTTP_FORBIDDEN);

        $taskColumn->tasks()->update([
            'task_column_id' => null,
            'status' => 'todo',
        ]);

        $taskColumn->delete();

        return back()->with('success', 'Colonne supprimee avec succes.');
    }
}
