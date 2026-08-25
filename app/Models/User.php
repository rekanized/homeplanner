<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'google_id', 'avatar', 'is_admin', 'is_child', 'accumulated_score', 'monthly_points_goal', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_child' => 'boolean',
        ];
    }

    /**
     * Check if the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Check if the user is the Master User.
     */
    public function isMaster(): bool
    {
        $configuredMaster = Setting::get('google_first_user_email');

        if (filled($configuredMaster)) {
            return strcasecmp($this->email, $configuredMaster) === 0;
        }

        return $this->id === self::oldest('id')->value('id');
    }

    /**
     * Get the chores assigned to the user.
     */
    public function chores()
    {
        return $this->hasMany(Chore::class);
    }

    /**
     * Get the monthly points for the child.
     */
    public function getMonthlyPointsAttribute(): int
    {
        if (array_key_exists('monthly_points', $this->attributes)) {
            return (int) $this->attributes['monthly_points'];
        }

        return Chore::where('user_id', $this->id)
            ->where('is_completed', true)
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->sum('score');
    }

    /**
     * Get the monthly goal progress percentage.
     */
    public function getMonthlyGoalProgressAttribute(): int
    {
        if (! $this->monthly_points_goal || $this->monthly_points_goal <= 0) {
            return 0;
        }
        $progress = ($this->monthlyPoints / $this->monthly_points_goal) * 100;

        return min(100, (int) $progress);
    }

    /**
     * Get the total chores finished by the child.
     */
    public function getTotalFinishedTasksAttribute(): int
    {
        return Chore::where('user_id', $this->id)
            ->where('is_completed', true)
            ->count();
    }

    /**
     * Get the chores finished by the child this month.
     */
    public function getMonthlyFinishedTasksAttribute(): int
    {
        if (array_key_exists('monthly_finished_tasks', $this->attributes)) {
            return (int) $this->attributes['monthly_finished_tasks'];
        }

        return Chore::where('user_id', $this->id)
            ->where('is_completed', true)
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();
    }

    public function scopeWithMonthlyChoreStats(Builder $query): Builder
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        return $query
            ->withSum([
                'chores as monthly_points' => fn (Builder $query) => $query
                    ->where('is_completed', true)
                    ->whereBetween('completed_at', [$start, $end]),
            ], 'score')
            ->withCount([
                'chores as monthly_finished_tasks' => fn (Builder $query) => $query
                    ->where('is_completed', true)
                    ->whereBetween('completed_at', [$start, $end]),
            ]);
    }
}
