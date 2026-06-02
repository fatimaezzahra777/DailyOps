<?php

namespace App\Services;

use App\Repositories\Contracts\ProjectRepositoryInterface;

class ProjectService
{
    protected $projectRepository;

    public function __construct(
        ProjectRepositoryInterface $projectRepository,
        protected AssignmentNotificationService $assignmentNotificationService,
    ) {
        $this->projectRepository = $projectRepository;
    }

    public function getAllProjects()
    {
        return $this->projectRepository->getAll();
    }

    public function getProjectById($id)
    {
        return $this->projectRepository->findById($id);
    }

    public function createProject(array $data)
    {
        $project = $this->projectRepository->store($data);

        $this->assignmentNotificationService->notifyProjectAssigned($project);

        return $project;
    }

    public function updateProject($id, array $data)
    {
        $project = $this->projectRepository->findById($id);
        $previousAssignedTo = $project->assigned_to;

        $project = $this->projectRepository->update($id, $data);

        if (($data['assigned_to'] ?? null) !== $previousAssignedTo) {
            $this->assignmentNotificationService->notifyProjectAssigned($project);
        }

        return $project;
    }

    public function deleteProject($id)
    {
        return $this->projectRepository->delete($id);
    }

    public function filterProjects($request)
    {
        return $this->projectRepository->searchAndFilter($request);
    }

    public function getFilteredProjectCollection($request)
    {
        return $this->projectRepository->getFilteredCollection($request);
    }
}
