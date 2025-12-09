<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceAiInsight extends Model
{
    protected $fillable = [
        'employee_id',
        'org_position_id',
        'generated_by',
        'context_type',
        'context_id',
        'model',
        'prompt',
        'response',
        'metrics',
        'payload',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'metrics' => 'array',
        'payload' => 'array',
        'expires_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}

