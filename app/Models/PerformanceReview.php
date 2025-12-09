<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceReview extends Model
{
    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'task_assignment_id',
        'review_type',
        'rating',
        'efficiency_score',
        'quality_score',
        'summary',
        'strengths',
        'improvements',
        'review_period_start',
        'review_period_end',
        'ai_snapshot',
        'score_breakdown',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'review_period_start' => 'date',
        'review_period_end' => 'date',
        'score_breakdown' => 'array',
        'metadata' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function assignment()
    {
        return $this->belongsTo(PerformanceTaskAssignment::class, 'task_assignment_id');
    }
}

