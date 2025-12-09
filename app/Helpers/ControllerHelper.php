<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class ControllerHelper
{
    /**
     * Get the query builder with proper created_by filtering for super admin
     * Super admin can see records with created_by = 0 OR created_by = their own ID
     * 
     * @param string $modelClass The model class name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getQueryForUser($modelClass)
    {
        $user = Auth::user();
        $query = $modelClass::query();
        
        if ($user->type == 'super admin') {
            // Super admin can see records with created_by = 0 OR created_by = their own ID
            $query->where(function($q) use ($user) {
                $q->where('created_by', '=', 0)
                  ->orWhere('created_by', '=', $user->creatorId());
            });
        } else {
            $query->where('created_by', '=', $user->creatorId());
        }
        
        return $query;
    }
}

