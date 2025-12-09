<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Console\Command;

class CleanupDuplicateHrmData extends Command
{
    protected $signature = 'cleanup:duplicate-hrm-data';
    protected $description = 'Remove duplicate HRM setup records (keep ones with proper mappings)';

    public function handle()
    {
        $this->info('Cleaning up duplicate HRM data...');

        // Remove departments without branch_id (keep ones with branch_id)
        $departmentsWithoutBranch = Department::whereNull('branch_id')->orWhere('branch_id', 0)->get();
        $deletedDepts = 0;
        foreach ($departmentsWithoutBranch as $dept) {
            // Check if there's a duplicate with branch_id
            $duplicate = Department::where('name', $dept->name)
                ->where('created_by', $dept->created_by)
                ->whereNotNull('branch_id')
                ->where('branch_id', '!=', 0)
                ->where('id', '!=', $dept->id)
                ->first();
            
            if ($duplicate) {
                $dept->delete();
                $deletedDepts++;
            }
        }
        $this->info("  ✓ Removed {$deletedDepts} duplicate departments");

        // Remove designations without department_id (keep ones with department_id)
        $designationsWithoutDept = Designation::whereNull('department_id')->orWhere('department_id', 0)->get();
        $deletedDesigs = 0;
        foreach ($designationsWithoutDept as $desig) {
            // Check if there's a duplicate with department_id
            $duplicate = Designation::where('name', $desig->name)
                ->where('created_by', $desig->created_by)
                ->whereNotNull('department_id')
                ->where('department_id', '!=', 0)
                ->where('id', '!=', $desig->id)
                ->first();
            
            if ($duplicate) {
                $desig->delete();
                $deletedDesigs++;
            }
        }
        $this->info("  ✓ Removed {$deletedDesigs} duplicate designations");

        $this->info("✅ Cleanup completed!");
        return 0;
    }
}

