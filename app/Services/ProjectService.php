<?php

namespace App\Services;

use App\Repositories\Contracts\ProjectRepositoryInterface;

class ProjectService
{
    protected $projectRepository;

    public function __construct(ProjectRepositoryInterface $projectRepository)
    {
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
        return $this->projectRepository->store($data);
    }

    public function updateProject($id, array $data)
    {
        return $this->projectRepository->update($id, $data);
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
