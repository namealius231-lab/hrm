<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgPosition extends Model
{
    protected $fillable = [
        'org_role_id',
        'reports_to_position_id',
        'department_id',
        'designation_id',
        'title',
        'code',
        'band',
        'level',
        'headcount',
        'responsibilities',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function role()
    {
        return $this->belongsTo(OrgRole::class, 'org_role_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'reports_to_position_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'reports_to_position_id');
    }

    public function assignments()
    {
        return $this->hasMany(EmployeePositionAssignment::class, 'org_position_id');
    }
}

