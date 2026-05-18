<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskRepository implements TaskRepositoryInterface
{
    public function getAll()
    {
        return Task::with('project')->latest()->paginate(10);
    }

    public function findById($id)
    {
        return Task::with('project')->findOrFail($id);
    }

    public function store(array $data)
    {
        return Task::create($data);
    }

    public function update($id, array $data)
    {
        $task = $this->findById($id);

        $task->update($data);

        return $task;
    }

    public function delete($id)
    {
        $task = $this->findById($id);

        return $task->delete();
    }

    public function searchAndFilter($request)
    {
        $query = Task::with('project');

        if ($request->search) {

            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->status) {

            $query->where('status', $request->status);
        }

        if ($request->priority) {

            $query->where('priority', $request->priority);
        }

        return $query->latest()->paginate(10);
    }
}