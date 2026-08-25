<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

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
        ];
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function tasksSummary(?string $period = null): Collection
    {
        [$start, $end] = match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'lastweek', 'last-week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'thismonth', 'this-month' => [now()->startOfMonth(), now()->endOfMonth()],
            'lastmonth', 'last-month' => [now()->startOfMonth()->subMonthsNoOverflow(), now()->subMonthsNoOverflow()->endOfMonth()],
            default => [now()->startOfWeek(), now()->endOfWeek()],
        };

        return $this->tasks()
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->get();
    }
}
