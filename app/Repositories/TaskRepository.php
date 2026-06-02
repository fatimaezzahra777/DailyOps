<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskRepository implements TaskRepositoryInterface
{
    public function getAll()
    {
        return Task::with(['project', 'comments'])->latest()->paginate(10)->withQueryString();
    }

    public function findById($id)
    {
        return Task::with(['project', 'comments' => fn ($query) => $query->latest()])->findOrFail($id);
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
        $query = Task::with(['project', 'comments']);

        if ($request->search) {

            $query->where(function ($builder) use ($request) {
                $builder
                    ->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('assigned_to', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status) {

            $query->where('status', $request->status);
        }

        if ($request->priority) {

            $query->where('priority', $request->priority);
        }

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        return $query->latest()->paginate(10)->withQueryString();
    }

    public function updateStatus($id, $status)
    {
        $task = $this->findById($id);

        $task->update([
            'status' => $status
        ]);

        return $task;
    }
}
