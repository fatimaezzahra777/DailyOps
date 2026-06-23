<?php

namespace App\Services;

use App\Mail\ProjectStatusUpdatedMail;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Support\Facades\Mail;

class ProjectService
{
    protected $projectRepository;

    public function __construct(
        ProjectRepositoryInterface $projectRepository,
        protected AssignmentNotificationService $assignmentNotificationService,
        protected ProjectInvitationService $projectInvitationService,
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
        $this->projectInvitationService->sendInvitation(
            $project,
            $project->assigned_to,
            $project->manager,
        );

        return $project;
    }

    public function updateProject($id, array $data)
    {
        $project = $this->projectRepository->findById($id);
        $previousAssignedTo = $project->assigned_to;
        $previousStatus = $project->status;

        $project = $this->projectRepository->update($id, $data);

        if (array_key_exists('assigned_to', $data) && ($data['assigned_to'] ?? null) !== $previousAssignedTo) {
            $this->assignmentNotificationService->notifyProjectAssigned($project);
            $this->projectInvitationService->sendInvitation(
                $project,
                $project->assigned_to,
                $project->manager,
            );
        }

        if (
            array_key_exists('status', $data)
            && ($data['status'] ?? null) !== $previousStatus
            && filled($project->client_email)
        ) {
            Mail::to($project->client_email)->send(new ProjectStatusUpdatedMail($project, $previousStatus));
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
