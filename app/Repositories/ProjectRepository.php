<?php

namespace App\Repositories;

use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function getAll()
    {
        return Project::latest()->paginate(10);
    }

    public function findById($id)
    {
        return Project::findOrFail($id);
    }

    public function store(array $data)
    {
        return Project::create($data);
    }
    
    public function update($id, array $data)
    {
        $project = $this->findById($id);

        $project->update($data);

        return $project;
    }

    public function delete($id)
    {
        $project = $this->findById($id);

        return $project->delete();
    }

    public function searchAndFilter($request)
    {
        $query = Project::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return $query->latest()->paginate(10);
    }
}