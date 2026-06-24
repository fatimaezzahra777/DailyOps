<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskRepository implements TaskRepositoryInterface
{
    public function getAll()
    {
        return Task::with(['project', 'comments', 'attachments', 'assignedUser', 'column'])->latest()->paginate(10)->withQueryString();
    }

    public function findById($id)
    {
        return Task::with([
            'project.collaborators',
            'comments' => fn ($query) => $query->latest(),
            'attachments' => fn ($query) => $query->with('user')->latest(),
            'assignedUser',
            'column',
        ])->findOrFail($id);
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
        $query = Task::with(['project', 'comments', 'attachments', 'assignedUser', 'column']);

        if ($request->user()) {
            $query->whereHas('project', fn ($projectQuery) => $projectQuery->visibleTo($request->user()));
        }

        if ($request->search) {

            $query->where(function ($builder) use ($request) {
                $builder
                    ->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('assigned_to', 'like', '%' . $request->search . '%')
                    ->orWhereHas('assignedUser', function ($userQuery) use ($request) {
                        $userQuery->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                    });
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

    public function updateStatus($id, $status, $taskColumnId = null)
    {
        $task = $this->findById($id);

        $task->update([
            'status' => $status,
            'task_column_id' => $taskColumnId,
        ]);

        return $task;
    }
}
