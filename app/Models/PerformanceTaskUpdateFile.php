<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceTaskUpdateFile extends Model
{
    protected $fillable = [
        'update_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function update()
    {
        return $this->belongsTo(PerformanceTaskUpdate::class, 'update_id');
    }
}

