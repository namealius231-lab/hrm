<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmployeePositionAssignment extends Model
{
    protected $fillable = [
        'employee_id',
        'org_position_id',
        'org_role_id',
        'reports_to_employee_id',
        'is_primary',
        'status',
        'effective_from',
        'effective_to',
        'metadata',
        'assigned_by',
        'created_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_primary' => 'boolean',
        'metadata' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'reports_to_employee_id');
    }

    public function orgRole()
    {
        return $this->belongsTo(OrgRole::class, 'org_role_id');
    }

    public function orgPosition()
    {
        return $this->belongsTo(OrgPosition::class, 'org_position_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}

