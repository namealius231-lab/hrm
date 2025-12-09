<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceTaskUpdate extends Model
{
    protected $fillable = [
        'task_assignment_id',
        'user_id',
        'status',
        'progress_percent',
        'summary',
        'strategy',
        'blockers',
        'evidence_path',
        'payload',
        'created_by',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function assignment()
    {
        return $this->belongsTo(PerformanceTaskAssignment::class, 'task_assignment_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function files()
    {
        return $this->hasMany(PerformanceTaskUpdateFile::class, 'update_id');
    }
}

