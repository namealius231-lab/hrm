<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Console\Command;

class VerifyHrmMapping extends Command
{
    protected $signature = 'verify:hrm-mapping';
    protected $description = 'Verify HRM data mapping (branches -> departments -> designations)';

    public function handle()
    {
        $this->info('Verifying HRM Data Mapping...');
        $this->info('');

        // Check Departments
        $this->info('Departments and their Branches:');
        $departments = Department::with('branch')->get();
        foreach ($departments as $dept) {
            $branchName = $dept->branch ? $dept->branch->name : 'NO BRANCH';
            $this->line("  - {$dept->name} -> Branch: {$branchName}");
        }
        $this->info('');

        // Check Designations
        $this->info('Designations and their Departments:');
        $designations = Designation::with('departments')->get();
        foreach ($designations as $desig) {
            $deptName = $desig->departments ? $desig->departments->name : 'NO DEPARTMENT';
            $this->line("  - {$desig->name} -> Department: {$deptName}");
        }

        return 0;
    }
}

