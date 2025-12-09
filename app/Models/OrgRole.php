<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgRole extends Model
{
    protected $fillable = [
        'name',
        'code',
        'role_id',
        'reports_to_role_id',
        'level',
        'rank_weight',
        'is_executive',
        'responsibilities',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'is_executive' => 'boolean',
        'metadata' => 'array',
    ];

    public function spatieRole()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'reports_to_role_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'reports_to_role_id');
    }

    public function positions()
    {
        return $this->hasMany(OrgPosition::class, 'org_role_id');
    }
}

