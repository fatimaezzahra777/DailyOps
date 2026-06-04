<?php

namespace App\Services;

use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskService
{
    protected $taskRepository;

    public function __construct(
        TaskRepositoryInterface $taskRepository,
        protected AssignmentNotificationService $assignmentNotificationService,
    ) {
        $this->taskRepository = $taskRepository;
    }

    public function getAllTasks()
    {
        return $this->taskRepository->getAll();
    }

    public function getTaskById($id)
    {
        return $this->taskRepository->findById($id);
    }

    public function createTask(array $data)
    {
        $task = $this->taskRepository->store($data);

        $this->assignmentNotificationService->notifyTaskAssigned($task);

        return $task;
    }

    public function updateTask($id, array $data)
    {
        $task = $this->taskRepository->findById($id);
        $previousAssignedUserId = $task->assigned_user_id;
        $previousAssignedTo = $task->assigned_to;

        $task = $this->taskRepository->update($id, $data);

        if (($data['assigned_user_id'] ?? null) !== $previousAssignedUserId || ($data['assigned_to'] ?? null) !== $previousAssignedTo) {
            $this->assignmentNotificationService->notifyTaskAssigned($task);
        }

        return $task;
    }

    public function deleteTask($id)
    {
        return $this->taskRepository->delete($id);
    }

    public function filterTasks($request)
    {
        return $this->taskRepository->searchAndFilter($request);
    }

    public function changeStatus($id, $status, $taskColumnId = null)
    {
        return $this->taskRepository->updateStatus($id, $status, $taskColumnId);
    }
}
