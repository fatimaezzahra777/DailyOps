<?php

namespace App\Services;

use App\Events\AssignmentNotificationSent;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class AssignmentNotificationService
{
    public function notifyTaskAssigned(Task $task): void
    {
        $user = $this->resolveAssignedUser($task->assigned_to);

        if (! $user) {
            return;
        }

        $this->broadcast($user, [
            'type' => 'task',
            'title' => 'Nouvelle tâche assignée',
            'message' => "La tâche « {$task->title} » vous a été assignée.",
            'url' => route('tasks.show', $task),
        ]);
    }

    public function notifyProjectAssigned(Project $project): void
    {
        $user = $this->resolveAssignedUser($project->assigned_to);

        if (! $user) {
            return;
        }

        $this->broadcast($user, [
            'type' => 'project',
            'title' => 'Nouveau projet assigné',
            'message' => "Le projet « {$project->name} » vous a été assigné.",
            'url' => route('projects.show', $project),
        ]);
    }

    protected function resolveAssignedUser(?string $assignedTo): ?User
    {
        $value = trim((string) $assignedTo);

        if ($value === '') {
            return null;
        }

        $normalized = mb_strtolower($value);

        return User::query()
            ->whereRaw('LOWER(email) = ?', [$normalized])
            ->orWhereRaw('LOWER(name) = ?', [$normalized])
            ->first();
    }

    protected function broadcast(User $user, array $notification): void
    {
        try {
            AssignmentNotificationSent::dispatch($user, $notification);
        } catch (Throwable $exception) {
            Log::warning('Realtime assignment notification could not be broadcast.', [
                'user_id' => $user->id,
                'notification_type' => $notification['type'] ?? null,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
