<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Meeting extends Model
{
    protected $fillable = [
        'organizer_id',
        'name',
        'title',
        'meeting_url',
        'scheduled_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where('organizer_id', $user->id)
                ->orWhereHas('participants', fn (Builder $query) => $query->whereKey($user->id));
        });
    }

    public function isOrganizedBy(User $user): bool
    {
        return $this->organizer_id === $user->id;
    }

    public function isVisibleTo(User $user): bool
    {
        return $this->isOrganizedBy($user)
            || $this->participants()->whereKey($user->id)->exists();
    }
}
