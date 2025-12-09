<?php

namespace App\Console\Commands;

use App\Models\AllowanceOption;
use App\Models\AwardType;
use App\Models\Branch;
use App\Models\Competencies;
use App\Models\ContractType;
use App\Models\DeductionOption;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Document;
use App\Models\ExpenseType;
use App\Models\GoalType;
use App\Models\IncomeType;
use App\Models\JobCategory;
use App\Models\JobStage;
use App\Models\LeaveType;
use App\Models\LoanOption;
use App\Models\PaymentType;
use App\Models\PayslipType;
use App\Models\Performance_Type;
use App\Models\TerminationType;
use App\Models\TrainingType;
use App\Models\User;
use Illuminate\Console\Command;

class UpdateHrmSetupCreatedBy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:hrm-setup-created-by';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all HRM setup records to have created_by = 0 for super admin visibility';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating HRM setup records to created_by = 0...');

        // Get super admin user to check
        $superAdmin = User::where('type', 'super admin')->first();
        if (!$superAdmin) {
            $this->warn('No super admin user found. Records will be set to created_by = 0.');
        }

        $models = [
            'Branch' => Branch::class,
            'Department' => Department::class,
            'Designation' => Designation::class,
            'LeaveType' => LeaveType::class,
            'Document' => Document::class,
            'PayslipType' => PayslipType::class,
            'AllowanceOption' => AllowanceOption::class,
            'LoanOption' => LoanOption::class,
            'DeductionOption' => DeductionOption::class,
            'GoalType' => GoalType::class,
            'TrainingType' => TrainingType::class,
            'AwardType' => AwardType::class,
            'TerminationType' => TerminationType::class,
            'JobCategory' => JobCategory::class,
            'JobStage' => JobStage::class,
            'Performance_Type' => Performance_Type::class,
            'Competencies' => Competencies::class,
            'ExpenseType' => ExpenseType::class,
            'IncomeType' => IncomeType::class,
            'PaymentType' => PaymentType::class,
            'ContractType' => ContractType::class,
        ];

        $totalUpdated = 0;
        foreach ($models as $name => $modelClass) {
            $count = $modelClass::where('created_by', '!=', 0)->update(['created_by' => 0]);
            if ($count > 0) {
                $this->line("  ✓ Updated {$count} {$name} records");
                $totalUpdated += $count;
            }
        }

        $this->info("✅ Updated {$totalUpdated} total records to created_by = 0");
        $this->info('Super admin should now be able to see all HRM setup data.');
        
        return 0;
    }
}

