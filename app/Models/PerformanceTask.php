<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PerformanceTask extends Model
{
    protected $fillable = [
        'public_id',
        'title',
        'description',
        'difficulty',
        'priority',
        'status',
        'visibility',
        'auto_overdue',
        'start_date',
        'deadline',
        'expected_hours',
        'buffer_hours',
        'assigned_by',
        'created_by',
        'ai_summary',
        'ai_summary_generated_at',
        'metadata',
    ];

    protected $casts = [
        'auto_overdue' => 'boolean',
        'start_date' => 'datetime',
        'deadline' => 'datetime',
        'ai_summary_generated_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (PerformanceTask $task) {
            if (empty($task->public_id)) {
                $task->public_id = (string) Str::uuid();
            }

            if (is_null($task->created_by) && auth()->check()) {
                $task->created_by = auth()->user()->creatorId();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignments()
    {
        return $this->hasMany(PerformanceTaskAssignment::class, 'task_id');
    }

    public function activeAssignments()
    {
        return $this->assignments()->whereNotIn('status', ['completed', 'archived']);
    }

    public function reviews()
    {
        return $this->hasManyThrough(
            PerformanceReview::class,
            PerformanceTaskAssignment::class,
            'task_id',
            'task_assignment_id'
        );
    }

    public function scopeOwnedBy(Builder $query, int $creatorId): Builder
    {
        return $query->where('created_by', $creatorId);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->type === 'company' || $user->type === 'hr') {
            return $query;
        }

        if ($user->type === 'employee' && $user->employee) {
            return $query->whereHas('assignments', function (Builder $builder) use ($user) {
                $builder->where('employee_id', $user->employee->id);
            });
        }

        return $query->where('assigned_by', $user->id);
    }
}

