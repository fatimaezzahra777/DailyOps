<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'manager_id',
        'column_id',
        'name',
        'description',
        'status',
        'assigned_to',
        'start_date',
        'end_date',
        'completed_at',
        'archived_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'completed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Project $project) {
            if ($project->status === 'completed') {
                if (! $project->completed_at) {
                    $project->completed_at = now();
                }

                return;
            }

            $project->completed_at = null;
            $project->archived_at = null;
        });
    }

    public static function archiveEligibleCompleted(): int
    {
        $now = now();

        return static::query()
            ->where('status', 'completed')
            ->whereNull('archived_at')
            ->whereNotNull('completed_at')
            ->where('completed_at', '<=', $now->copy()->subDays(5))
            ->update([
                'archived_at' => $now,
                'updated_at' => $now,
            ]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function taskColumns(): HasMany
    {
        return $this->hasMany(TaskColumn::class);
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(ProjectColumn::class, 'column_id');
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['invited_by', 'accepted_at'])
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ProjectInvitation::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($user) {
            $query->where('manager_id', $user->id)
                ->orWhereHas('collaborators', fn (Builder $query) => $query->whereKey($user->id))
                ->orWhere(function (Builder $query) use ($user) {
                    $query->whereIn('assigned_to', array_filter([$user->name, $user->email]))
                        ->whereDoesntHave('invitations', function (Builder $query) use ($user) {
                            $query->where('email', $user->email)
                                ->where('status', ProjectInvitation::STATUS_PENDING);
                        });
                });
        });
    }

    public function isVisibleTo(User $user): bool
    {
        if ($user->isAdmin() || $this->manager_id === $user->id) {
            return true;
        }

        if ($this->collaborators()->where('users.id', $user->id)->exists()) {
            return true;
        }

        $hasPendingInvitation = $this->invitations()
            ->where('email', $user->email)
            ->where('status', ProjectInvitation::STATUS_PENDING)
            ->exists();

        return $user->isAdmin()
            || (! $hasPendingInvitation && in_array($this->assigned_to, array_filter([$user->name, $user->email]), true));
    }

    public function isManagedBy(User $user): bool
    {
        return $user->isAdmin() || $this->manager_id === $user->id;
    }
}
