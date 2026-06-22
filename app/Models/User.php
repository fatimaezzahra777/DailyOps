<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'birth_date',
        'email_verified_at',
        'password',
        'role',
        'theme_color',
        'font_size',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'birthday_reminder_sent_for' => 'date',
            'password' => 'hashed',
        ];
    }

    public function managedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'manager_id');
    }

    public function collaborativeProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot(['invited_by', 'accepted_at'])
            ->withTimestamps();
    }

    public function projectInvitations(): HasMany
    {
        return $this->hasMany(ProjectInvitation::class);
    }

    public function projectColumns(): HasMany
    {
        return $this->hasMany(ProjectColumn::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function themeColor(): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', (string) $this->theme_color)
            ? strtolower($this->theme_color)
            : '#c50064';
    }

    public function fontScale(): float
    {
        return match ($this->font_size) {
            'small' => 0.9,
            'large' => 1.12,
            default => 1,
        };
    }

    public function hasBirthdayOn(CarbonInterface $date): bool
    {
        if (! $this->birth_date) {
            return false;
        }

        if ($this->birth_date->format('m-d') === '02-29' && ! $date->isLeapYear()) {
            return $date->format('m-d') === '02-28';
        }

        return $this->birth_date->format('m-d') === $date->format('m-d');
    }
}
