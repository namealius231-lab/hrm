<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $fillable = [
        'branch_id',
        'department_id',
        'name',
        'hierarchy_track',
        'hierarchy_level',
        'reports_to_designation_id',
        'rank_weight',
        'created_by',
    ];

    public function branch(){
        return $this->hasOne('App\Models\Branch','id','branch_id');
    }

    public function departments()
    {
        return $this->hasOne('App\Models\Department', 'id', 'department_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'reports_to_designation_id');
    }
}
