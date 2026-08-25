<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $fillable = ['name', 'priority_id', 'due_date'];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function scopeHandleSort(Builder $query, string $column)
    {
        $query
            ->when($column === 'name', function ($query) {
                $query->orderBy('name');
            })
            ->when($column === 'time', function ($query) {
                $query->latest();
            })
            ->when($column === 'priority', function ($query) {
                $query->orderByRaw('CASE WHEN priority_id IS NULL THEN 1 ELSE 0 END, 
        priority_id ASC');
            });
    }

    public function scopeHandleFilter(Builder $query, ?string $dueDate)
    {
        $query
            ->when($dueDate === 'today', function ($query) {
                $from = now()->startOfDay();
                $to = $from->copy()->endOfDay();
                
                $query->whereBetween('due_date', [$from, $to])
                    ->orWhereNull('due_date');
            })
            ->when($dueDate === 'overdue', function ($query) {
                $query->where('due_date', '<', now()->startOfDay());
            });
    }
}
