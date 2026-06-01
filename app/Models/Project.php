<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(ProjectColumn::class, 'column_id');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($user) {
            $query->where('manager_id', $user->id)
                ->orWhereIn('assigned_to', array_filter([$user->name, $user->email]));
        });
    }

    public function isVisibleTo(User $user): bool
    {
        return $user->isAdmin()
            || $this->manager_id === $user->id
            || in_array($this->assigned_to, array_filter([$user->name, $user->email]), true);
    }
}
