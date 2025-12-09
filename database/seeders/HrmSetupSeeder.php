<?php

namespace Database\Seeders;

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
use Illuminate\Database\Seeder;

class HrmSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first company or super admin user for created_by
        // For super admin, we use created_by = 0 so all super admins can see the data
        $user = User::where('type', 'company')->orWhere('type', 'super admin')->first();
        
        if (!$user) {
            $this->command->warn('No company or super admin user found. Creating dummy data with created_by = 0');
            $createdBy = 0;
        } else {
            // Use 0 for super admin so all super admins can see the seeded data
            // For company users, use their creatorId
            if ($user->type == 'super admin') {
                $createdBy = 0;
            } else {
                $createdBy = $user->creatorId();
            }
        }

        $this->command->info('Seeding HRM Setup Data...');

        // 1. Branches
        $branches = [
            'Head Office',
            'New York Branch',
            'Los Angeles Branch',
            'Chicago Branch',
            'Houston Branch',
        ];
        $branchIds = [];
        foreach ($branches as $branchName) {
            $branch = Branch::firstOrCreate(
                ['name' => $branchName, 'created_by' => $createdBy],
                ['name' => $branchName, 'created_by' => $createdBy]
            );
            $branchIds[] = $branch->id;
        }
        $this->command->info('✓ Branches seeded');

        // 2. Departments - Map to branches
        $departments = [
            ['name' => 'Human Resources', 'branch' => 'Head Office'],
            ['name' => 'Information Technology', 'branch' => 'Head Office'],
            ['name' => 'Finance & Accounting', 'branch' => 'Head Office'],
            ['name' => 'Sales & Marketing', 'branch' => 'New York Branch'],
            ['name' => 'Operations', 'branch' => 'Los Angeles Branch'],
            ['name' => 'Customer Service', 'branch' => 'Chicago Branch'],
            ['name' => 'Research & Development', 'branch' => 'Houston Branch'],
            ['name' => 'Legal & Compliance', 'branch' => 'Head Office'],
        ];
        $departmentIds = [];
        foreach ($departments as $dept) {
            $branch = Branch::where('name', $dept['branch'])->where('created_by', $createdBy)->first();
            if (!$branch && $createdBy == 0) {
                $branch = Branch::where('name', $dept['branch'])->first();
            }
            $branchId = $branch ? $branch->id : ($branchIds[0] ?? 1);
            
            // Update existing or create new
            $department = Department::where('name', $dept['name'])->where('created_by', $createdBy)->first();
            if (!$department && $createdBy == 0) {
                $department = Department::where('name', $dept['name'])->first();
            }
            
            if ($department) {
                $department->branch_id = $branchId;
                $department->created_by = $createdBy;
                $department->save();
            } else {
                $department = Department::create([
                    'name' => $dept['name'],
                    'branch_id' => $branchId,
                    'created_by' => $createdBy
                ]);
            }
            $departmentIds[] = $department->id;
        }
        $this->command->info('✓ Departments seeded and mapped to branches');

        // 3. Designations - Map to departments
        $designations = [
            ['name' => 'Chief Executive Officer', 'department' => 'Human Resources'],
            ['name' => 'Chief Technology Officer', 'department' => 'Information Technology'],
            ['name' => 'Chief Financial Officer', 'department' => 'Finance & Accounting'],
            ['name' => 'HR Manager', 'department' => 'Human Resources'],
            ['name' => 'IT Manager', 'department' => 'Information Technology'],
            ['name' => 'Finance Manager', 'department' => 'Finance & Accounting'],
            ['name' => 'Sales Manager', 'department' => 'Sales & Marketing'],
            ['name' => 'Operations Manager', 'department' => 'Operations'],
            ['name' => 'Senior Developer', 'department' => 'Information Technology'],
            ['name' => 'Junior Developer', 'department' => 'Information Technology'],
            ['name' => 'Accountant', 'department' => 'Finance & Accounting'],
            ['name' => 'Sales Executive', 'department' => 'Sales & Marketing'],
            ['name' => 'Customer Support Representative', 'department' => 'Customer Service'],
            ['name' => 'Marketing Specialist', 'department' => 'Sales & Marketing'],
        ];
        foreach ($designations as $desig) {
            $department = Department::where('name', $desig['department'])->where('created_by', $createdBy)->first();
            if (!$department && $createdBy == 0) {
                $department = Department::where('name', $desig['department'])->first();
            }
            $departmentId = $department ? $department->id : ($departmentIds[0] ?? 1);
            $branchId = $department ? $department->branch_id : ($branchIds[0] ?? 1);
            
            // Update existing or create new
            $designation = Designation::where('name', $desig['name'])->where('created_by', $createdBy)->first();
            if (!$designation && $createdBy == 0) {
                $designation = Designation::where('name', $desig['name'])->first();
            }
            
            if ($designation) {
                $designation->department_id = $departmentId;
                $designation->branch_id = $branchId;
                $designation->created_by = $createdBy;
                $designation->save();
            } else {
                $designation = Designation::create([
                    'name' => $desig['name'],
                    'branch_id' => $branchId,
                    'department_id' => $departmentId,
                    'created_by' => $createdBy
                ]);
            }
        }
        $this->command->info('✓ Designations seeded and mapped to departments');

        // 4. Leave Types
        $leaveTypes = [
            ['title' => 'Annual Leave', 'days' => 20],
            ['title' => 'Sick Leave', 'days' => 10],
            ['title' => 'Casual Leave', 'days' => 12],
            ['title' => 'Maternity Leave', 'days' => 90],
            ['title' => 'Paternity Leave', 'days' => 5],
            ['title' => 'Personal Leave', 'days' => 5],
            ['title' => 'Emergency Leave', 'days' => 3],
        ];
        foreach ($leaveTypes as $leaveType) {
            LeaveType::firstOrCreate(
                ['title' => $leaveType['title'], 'created_by' => $createdBy],
                ['title' => $leaveType['title'], 'days' => $leaveType['days'], 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Leave Types seeded');

        // 5. Document Types
        $documentTypes = [
            ['name' => 'Passport', 'is_required' => 'on'],
            ['name' => 'ID Card', 'is_required' => 'on'],
            ['name' => 'Driver License', 'is_required' => 'off'],
            ['name' => 'Educational Certificate', 'is_required' => 'on'],
            ['name' => 'Employment Contract', 'is_required' => 'on'],
            ['name' => 'Bank Statement', 'is_required' => 'off'],
            ['name' => 'Medical Certificate', 'is_required' => 'off'],
        ];
        foreach ($documentTypes as $docType) {
            Document::firstOrCreate(
                ['name' => $docType['name'], 'created_by' => $createdBy],
                ['name' => $docType['name'], 'is_required' => $docType['is_required'], 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Document Types seeded');

        // 6. Payslip Types
        $payslipTypes = [
            'Monthly',
            'Bi-Weekly',
            'Weekly',
            'Daily',
        ];
        foreach ($payslipTypes as $payslipType) {
            PayslipType::firstOrCreate(
                ['name' => $payslipType, 'created_by' => $createdBy],
                ['name' => $payslipType, 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Payslip Types seeded');

        // 7. Allowance Options
        $allowanceOptions = [
            'Transport Allowance',
            'Medical Allowance',
            'Housing Allowance',
            'Meal Allowance',
            'Communication Allowance',
            'Travel Allowance',
            'Entertainment Allowance',
        ];
        foreach ($allowanceOptions as $allowance) {
            AllowanceOption::firstOrCreate(
                ['name' => $allowance, 'created_by' => $createdBy],
                ['name' => $allowance, 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Allowance Options seeded');

        // 8. Loan Options
        $loanOptions = [
            'Personal Loan',
            'Home Loan',
            'Car Loan',
            'Education Loan',
            'Medical Loan',
            'Emergency Loan',
        ];
        foreach ($loanOptions as $loan) {
            LoanOption::firstOrCreate(
                ['name' => $loan, 'created_by' => $createdBy],
                ['name' => $loan, 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Loan Options seeded');

        // 9. Deduction Options
        $deductionOptions = [
            'Tax Deduction',
            'Provident Fund',
            'Insurance Premium',
            'Loan Deduction',
            'Advance Salary',
            'Late Coming Deduction',
            'Absent Deduction',
        ];
        foreach ($deductionOptions as $deduction) {
            DeductionOption::firstOrCreate(
                ['name' => $deduction, 'created_by' => $createdBy],
                ['name' => $deduction, 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Deduction Options seeded');

        // 10. Goal Types
        $goalTypes = [
            'Sales Target',
            'Project Completion',
            'Performance Improvement',
            'Skill Development',
            'Team Building',
            'Customer Satisfaction',
        ];
        foreach ($goalTypes as $goalType) {
            GoalType::firstOrCreate(
                ['name' => $goalType, 'created_by' => $createdBy],
                ['name' => $goalType, 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Goal Types seeded');

        // 11. Training Types
        $trainingTypes = [
            'Onboarding Training',
            'Technical Training',
            'Soft Skills Training',
            'Leadership Training',
            'Safety Training',
            'Compliance Training',
            'Product Training',
        ];
        foreach ($trainingTypes as $trainingType) {
            TrainingType::firstOrCreate(
                ['name' => $trainingType, 'created_by' => $createdBy],
                ['name' => $trainingType, 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Training Types seeded');

        // 12. Award Types
        $awardTypes = [
            'Employee of the Month',
            'Best Performer',
            'Long Service Award',
            'Innovation Award',
            'Team Player Award',
            'Customer Service Excellence',
            'Sales Achievement',
        ];
        foreach ($awardTypes as $awardType) {
            AwardType::firstOrCreate(
                ['name' => $awardType, 'created_by' => $createdBy],
                ['name' => $awardType, 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Award Types seeded');

        // 13. Termination Types
        $terminationTypes = [
            'Resignation',
            'Retirement',
            'Termination for Cause',
            'Layoff',
            'End of Contract',
            'Mutual Agreement',
        ];
        foreach ($terminationTypes as $terminationType) {
            TerminationType::firstOrCreate(
                ['name' => $terminationType, 'created_by' => $createdBy],
                ['name' => $terminationType, 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Termination Types seeded');

        // 14. Job Categories
        $jobCategories = [
            'Information Technology',
            'Sales & Marketing',
            'Human Resources',
            'Finance & Accounting',
            'Operations',
            'Customer Service',
            'Engineering',
            'Management',
        ];
        foreach ($jobCategories as $category) {
            JobCategory::firstOrCreate(
                ['title' => $category, 'created_by' => $createdBy],
                ['title' => $category, 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Job Categories seeded');

        // 15. Job Stages
        $jobStages = [
            ['title' => 'Applied', 'order' => 1],
            ['title' => 'Phone Screening', 'order' => 2],
            ['title' => 'Interview', 'order' => 3],
            ['title' => 'Technical Test', 'order' => 4],
            ['title' => 'Final Interview', 'order' => 5],
            ['title' => 'Offer', 'order' => 6],
            ['title' => 'Hired', 'order' => 7],
            ['title' => 'Rejected', 'order' => 8],
        ];
        foreach ($jobStages as $stage) {
            JobStage::firstOrCreate(
                ['title' => $stage['title'], 'created_by' => $createdBy],
                ['title' => $stage['title'], 'order' => $stage['order'], 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Job Stages seeded');

        // 16. Performance Types
        $performanceTypes = [
            'Technical Skills',
            'Communication',
            'Leadership',
            'Problem Solving',
            'Teamwork',
            'Time Management',
            'Creativity',
        ];
        foreach ($performanceTypes as $perfType) {
            Performance_Type::firstOrCreate(
                ['name' => $perfType, 'created_by' => $createdBy],
                ['name' => $perfType, 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Performance Types seeded');

        // 17. Competencies (needs Performance Type)
        $performanceTypeIds = Performance_Type::where('created_by', $createdBy)->pluck('id')->toArray();
        if (!empty($performanceTypeIds)) {
            $competencies = [
                ['name' => 'JavaScript Proficiency', 'type' => $performanceTypeIds[0] ?? null],
                ['name' => 'Public Speaking', 'type' => $performanceTypeIds[1] ?? null],
                ['name' => 'Team Management', 'type' => $performanceTypeIds[2] ?? null],
                ['name' => 'Analytical Thinking', 'type' => $performanceTypeIds[3] ?? null],
                ['name' => 'Collaboration', 'type' => $performanceTypeIds[4] ?? null],
                ['name' => 'Project Planning', 'type' => $performanceTypeIds[5] ?? null],
                ['name' => 'Design Thinking', 'type' => $performanceTypeIds[6] ?? null],
            ];
            foreach ($competencies as $competency) {
                if ($competency['type']) {
                    Competencies::firstOrCreate(
                        ['name' => $competency['name'], 'type' => $competency['type'], 'created_by' => $createdBy],
                        ['name' => $competency['name'], 'type' => $competency['type'], 'created_by' => $createdBy]
                    );
                }
            }
            $this->command->info('✓ Competencies seeded');
        }

        // 18. Expense Types
        $expenseTypes = [
            'Travel',
            'Meals',
            'Accommodation',
            'Office Supplies',
            'Equipment',
            'Training',
            'Marketing',
            'Utilities',
        ];
        foreach ($expenseTypes as $expenseType) {
            ExpenseType::firstOrCreate(
                ['name' => $expenseType, 'created_by' => $createdBy],
                ['name' => $expenseType, 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Expense Types seeded');

        // 19. Income Types
        $incomeTypes = [
            'Service Revenue',
            'Product Sales',
            'Consulting Fees',
            'Interest Income',
            'Rental Income',
            'Commission',
            'Other Income',
        ];
        foreach ($incomeTypes as $incomeType) {
            IncomeType::firstOrCreate(
                ['name' => $incomeType, 'created_by' => $createdBy],
                ['name' => $incomeType, 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Income Types seeded');

        // 20. Payment Types
        $paymentTypes = [
            'Cash',
            'Bank Transfer',
            'Credit Card',
            'Debit Card',
            'Check',
            'Online Payment',
            'Mobile Payment',
        ];
        foreach ($paymentTypes as $paymentType) {
            PaymentType::firstOrCreate(
                ['name' => $paymentType, 'created_by' => $createdBy],
                ['name' => $paymentType, 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Payment Types seeded');

        // 21. Contract Types
        $contractTypes = [
            'Permanent',
            'Contract',
            'Temporary',
            'Part-Time',
            'Internship',
            'Consultant',
        ];
        foreach ($contractTypes as $contractType) {
            ContractType::firstOrCreate(
                ['name' => $contractType, 'created_by' => $createdBy],
                ['name' => $contractType, 'created_by' => $createdBy]
            );
        }
        $this->command->info('✓ Contract Types seeded');

        $this->command->info('');
        $this->command->info('✅ HRM Setup Data seeding completed successfully!');
        $this->command->info('');
    }
}

