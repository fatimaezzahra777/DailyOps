<?php

namespace App\Services;

use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskService
{
    protected $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
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
        return $this->taskRepository->store($data);
    }

    public function updateTask($id, array $data)
    {
        return $this->taskRepository->update($id, $data);
    }

    public function deleteTask($id)
    {
        return $this->taskRepository->delete($id);
    }

    public function filterTasks($request)
    {
        return $this->taskRepository->searchAndFilter($request);
    }
}