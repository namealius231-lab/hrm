<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PerformanceTaskAssignment extends Model
{
    protected $fillable = [
        'task_id',
        'employee_id',
        'org_role_id',
        'org_position_id',
        'status',
        'progress_percent',
        'started_at',
        'completed_at',
        'turnaround_minutes',
        'last_progress_note',
        'last_progress_at',
        'last_activity_by',
        'created_by',
        'sla',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_progress_at' => 'datetime',
        'sla' => 'array',
    ];

    public function task()
    {
        return $this->belongsTo(PerformanceTask::class, 'task_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function orgRole()
    {
        return $this->belongsTo(OrgRole::class, 'org_role_id');
    }

    public function orgPosition()
    {
        return $this->belongsTo(OrgPosition::class, 'org_position_id');
    }

    public function lastActor()
    {
        return $this->belongsTo(User::class, 'last_activity_by');
    }

    public function updates()
    {
        return $this->hasMany(PerformanceTaskUpdate::class, 'task_assignment_id')->orderByDesc('id');
    }

    public function latestUpdate()
    {
        return $this->hasOne(PerformanceTaskUpdate::class, 'task_assignment_id')->latestOfMany();
    }

    public function reviews()
    {
        return $this->hasMany(PerformanceReview::class, 'task_assignment_id');
    }

    public function scopeForEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['completed', 'archived', 'cancelled']);
    }

    public function markProgress(int $percent, string $status, ?Carbon $completedAt = null): void
    {
        $this->progress_percent = $percent;
        $this->status = $status;
        if ($completedAt) {
            $this->completed_at = $completedAt;
            if ($this->started_at) {
                $this->turnaround_minutes = $this->started_at->diffInMinutes($completedAt);
            }
        }
        $this->save();
    }
}

