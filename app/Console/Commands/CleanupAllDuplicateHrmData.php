<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupAllDuplicateHrmData extends Command
{
    protected $signature = 'cleanup:all-duplicate-hrm-data';
    protected $description = 'Remove all duplicate HRM setup records, keep only ones with created_by = 0';

    public function handle()
    {
        $this->info('Cleaning up ALL duplicate HRM data (keeping created_by = 0)...');

        // For departments: keep only ones with created_by = 0, remove others with same name
        $deptNames = Department::where('created_by', 0)->pluck('name')->unique();
        $deletedDepts = 0;
        foreach ($deptNames as $name) {
            $keep = Department::where('name', $name)->where('created_by', 0)->first();
            if ($keep) {
                $duplicates = Department::where('name', $name)
                    ->where('id', '!=', $keep->id)
                    ->get();
                foreach ($duplicates as $dup) {
                    $dup->delete();
                    $deletedDepts++;
                }
            }
        }
        $this->info("  ✓ Removed {$deletedDepts} duplicate departments");

        // For designations: keep only ones with created_by = 0, remove others with same name
        $desigNames = Designation::where('created_by', 0)->pluck('name')->unique();
        $deletedDesigs = 0;
        foreach ($desigNames as $name) {
            $keep = Designation::where('name', $name)->where('created_by', 0)->first();
            if ($keep) {
                $duplicates = Designation::where('name', $name)
                    ->where('id', '!=', $keep->id)
                    ->get();
                foreach ($duplicates as $dup) {
                    $dup->delete();
                    $deletedDesigs++;
                }
            }
        }
        $this->info("  ✓ Removed {$deletedDesigs} duplicate designations");

        $this->info("✅ Cleanup completed!");
        return 0;
    }
}

